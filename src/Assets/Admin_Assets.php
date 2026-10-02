<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Interactive_Fields;
use WP_Screen;

class Admin_Assets extends Hookable implements Hooks_Interface {
	private Asset_Resolver $asset_resolver;
	/**
	 * @var Cpt_Interactive_Fields[]
	 */
	private array $interactive_fields;

	/**
	 * @param Cpt_Interactive_Fields[] $interactive_fields
	 */
	public function __construct(
		Asset_Resolver $asset_resolver,
		array $interactive_fields
	) {
		$this->asset_resolver     = $asset_resolver;
		$this->interactive_fields = $interactive_fields;
	}

	public function enqueue_admin_scripts(): void {
		$current_screen = get_current_screen();

		if ( $current_screen instanceof WP_Screen &&
			self::is_target_screen() ) {
			$this->enqueue_admin_assets( $current_screen->base );
		}
	}

	public function enqueue_editor_styles(): void {
		if ( self::is_target_screen() ) {
			$plugin_prefix = Hard_Layout_Cpt::cpt_name();
			$style_handle  = sprintf( '%s_editor', $plugin_prefix );
			$style_url     = $this->asset_resolver->get_asset_url( 'css/admin/editor.min.css' );
			$version       = $this->asset_resolver->get_version();

			wp_enqueue_style( $style_handle, $style_url, array(), $version );
		}
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		if ( $route_detector->is_admin_route() ) {
			self::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			self::add_action( 'enqueue_block_assets', array( $this, 'enqueue_editor_styles' ) );
		}
	}

	protected function enqueue_code_editor(): void {
		$plugin_prefix = Hard_Layout_Cpt::cpt_name();
		$ace_handle    = sprintf( '%s_ace', $plugin_prefix );
		$ace_url       = $this->asset_resolver->get_asset_url( 'js/admin/code-editor/ace.js' );
		$version       = $this->asset_resolver->get_version();
		$script_args   = array(
			'in_footer' => true,
		);

		wp_enqueue_script( $ace_handle, $ace_url, array(), $version, $script_args );

		$extensions = array( 'ext-beautify', 'ext-language_tools', 'ext-linking' );

		foreach ( $extensions as $extension ) {
			$extension_handle = sprintf( '%s_ace-%s', $plugin_prefix, $extension );
			$extension_path   = sprintf( 'js/admin/code-editor/%s.js', $extension );
			$extension_url    = $this->asset_resolver->get_asset_url( $extension_path );

			wp_enqueue_script( $extension_handle, $extension_url, array( $ace_handle ), $version, $script_args );
		}
	}

	protected function get_cpt_item_js_file_url(): string {
		return $this->asset_resolver->get_asset_url( 'js/admin/cpt-item.min.js' );
	}

	/**
	 * @param array<string,mixed> $js_data
	 */
	protected function enqueue_admin_assets( string $current_base, array $js_data = array() ): void {
		$plugin_prefix = Hard_Layout_Cpt::cpt_name();
		$version       = $this->asset_resolver->get_version();

		$tools_base    = sprintf( '%s_page_avf-tools', $plugin_prefix );
		$settings_base = sprintf( '%s_page_avf-settings', $plugin_prefix );

		$enqueuers = array(
			// add, edit pages.
			'post'         => fn() => $this->enqueue_cpt_item_assets( $js_data ),
			// 'edit' means 'list page'
			'edit'         => fn() => $this->enqueue_list_page_style(),
			$tools_base    => fn() => $this->enqueue_tools_style(),
			$settings_base => fn() => $this->enqueue_tools_style(),
		);

		if ( key_exists( $current_base, $enqueuers ) ) {
			$enqueuers[ $current_base ]();
		}

		$plugin_page_begins = sprintf( '%s_page_', $plugin_prefix );

		// 'dashboard' for all the custom pages (but not for edit/add pages)
		if ( 0 === strpos( $current_base, $plugin_page_begins ) ) {
			$style_handle = sprintf( '%s_page', $plugin_prefix );
			$style_url    = $this->asset_resolver->get_asset_url( 'css/admin/dashboard.min.css' );

			wp_enqueue_style( $style_handle, $style_url, array(), $version );
		}

		// plugin-header for all the pages without exception.
		$common_handle = sprintf( '%s_common', $plugin_prefix );
		$common_url    = $this->asset_resolver->get_asset_url( 'css/admin/common.min.css' );

		wp_enqueue_style( $common_handle, $common_url, array(), $version );
	}

	protected function enqueue_list_page_style(): void {
		$plugin_prefix = Hard_Layout_Cpt::cpt_name();
		$style_handle  = sprintf( '%s_list-page', $plugin_prefix );
		$style_url     = $this->asset_resolver->get_asset_url( 'css/admin/list-page.min.css' );
		$version       = $this->asset_resolver->get_version();

		wp_enqueue_style( $style_handle, $style_url, array(), $version );
	}

	protected function enqueue_tools_style(): void {
		$plugin_prefix = Hard_Layout_Cpt::cpt_name();
		$style_handle  = sprintf( '%s_tools', $plugin_prefix );
		$style_url     = $this->asset_resolver->get_asset_url( 'css/admin/tools.min.css' );
		$version       = $this->asset_resolver->get_version();

		wp_enqueue_style( $style_handle, $style_url, array(), $version );
	}

	/**
	 * @param array<string,mixed> $js_data
	 */
	protected function enqueue_cpt_item_assets( array $js_data ): void {
		global $post;

		$plugin_prefix = Hard_Layout_Cpt::cpt_name();
		$version       = $this->asset_resolver->get_version();
		$post_type     = $post->post_type;
		$page_js_data  = $this->resolve_page_js_data( $post_type );
		$js_data       = array_merge_recursive( $js_data, $page_js_data );

		$this->enqueue_code_editor();

		$item_handle = sprintf( '%s_cpt-item', $plugin_prefix );
		$style_url   = $this->asset_resolver->get_asset_url( 'css/admin/cpt-item.min.css' );

		wp_enqueue_style( $item_handle, $style_url, array(), $version );

		$ace_handle  = sprintf( '%s_ace', $plugin_prefix );
		$script_url  = $this->get_cpt_item_js_file_url();
		$script_args = array(
			// in footer, so if we need to include others, like 'ace.js' we can include in header.
			'in_footer' => true,
		);
		// jquery is necessary for select2 events; make sure acf and ACE editor are loaded.
		$script_deps = array( 'jquery', 'acf-input', $ace_handle, 'wp-api-fetch' );

		wp_enqueue_script( $item_handle, $script_url, $script_deps, $version, $script_args );
		wp_localize_script( $item_handle, 'acf_views', $js_data );
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function resolve_page_js_data( string $post_type ): array {
		foreach ( $this->interactive_fields as $interactive_fields ) {
			if ( $post_type === $interactive_fields->get_cpt()->cpt_name() ) {
				return $interactive_fields->get_page_js_data();
			}
		}

		return array();
	}

	protected static function is_target_screen(): bool {
		// can be missing, when called via Rest API by SiteGround_Optimizer in the 'enqueue_block_assets' hook.
		$current_screen = function_exists( 'get_current_screen' ) ?
			get_current_screen() :
			null;

		if ( $current_screen instanceof WP_Screen ) {
			$target_cpts = array( Hard_Layout_Cpt::cpt_name(), Hard_Post_Selection_Cpt::cpt_name() );

			return in_array( $current_screen->id, $target_cpts, true ) ||
				in_array( $current_screen->post_type, $target_cpts, true );
		}

		return false;
	}
}
