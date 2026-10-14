<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Resolver\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;

abstract class Library_Pattern_Base implements Library_Pattern {
	const NAME = '';

	/**
	 * @var array<string, bool>
	 */
	private array $js_handles;
	/**
	 * @var array<string, bool>
	 */
	private array $css_handles;
	private Asset_Resolver $asset_resolver;
	private string $auto_discover_name;

	public function __construct( Asset_Resolver $asset_resolver ) {
		$this->asset_resolver     = $asset_resolver;
		$this->js_handles         = array();
		$this->css_handles        = array();
		$this->auto_discover_name = '';
	}

	protected function print_code_piece( string $name, string $piece_safe ): void {
		echo "\n\n";
		printf( '/* %s : %s (auto-discover-begin) */', esc_html( $this->auto_discover_name ), esc_html( $name ) );
		echo "\n\n";
		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $piece_safe;
		echo "\n\n";
		printf( '/* %s : %s (auto-discover-end) */', esc_html( $this->auto_discover_name ), esc_html( $name ) );
		echo "\n\n";
	}

	protected function get_asset_url( string $file ): string {
		return $this->asset_resolver->get_asset_url( $file );
	}

	protected function get_asset_path( string $file ): string {
		return $this->asset_resolver->get_asset_path( $file );
	}

	protected function print_js_code_piece(
		string $name,
		string $piece_safe,
		string $field_selector,
		bool $is_multiple
	): void {
		ob_start();

		if ( $is_multiple ) {
			printf( "this.querySelectorAll('%s').forEach(item => {\n", esc_html( $field_selector ) );
		} else {
			printf( "var %s = this.querySelector('%s');\n", esc_html( $name ), esc_html( $field_selector ) );
			printf( "if (%s) {\n", esc_html( $name ) );
		}

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $piece_safe;
		echo $is_multiple ?
			"\n});" :
			"\n}";

		$js_code_safe = (string) ob_get_clean();

		$this->print_code_piece( $name, $js_code_safe );
	}

	protected static function get_wp_handle( string $handle ): string {
		$cpt_name = Hard_Layout_Cpt::cpt_name();

		return sprintf( '%s_%s', $cpt_name, $handle );
	}

	protected function get_asset_resolver(): Asset_Resolver {
		return $this->asset_resolver;
	}

	/**
	 * @param array<string, bool> $js_handles
	 */
	protected function set_js_handles( array $js_handles ): void {
		$this->js_handles = $js_handles;
	}

	protected function enable_js_handle( string $js_handle ): void {
		$this->js_handles[ $js_handle ] = true;
	}

	protected function enable_css_handle( string $css_handle ): void {
		$this->css_handles[ $css_handle ] = true;
	}

	protected function is_enabled_js_handle( string $js_handle ): bool {
		return key_exists( $js_handle, $this->js_handles ) &&
				$this->js_handles[ $js_handle ];
	}

	/**
	 * @param array<string, bool> $css_handles
	 */
	protected function set_css_handles( array $css_handles ): void {
		$this->css_handles = $css_handles;
	}

	protected function set_auto_discover_name( string $auto_discover_name ): void {
		$this->auto_discover_name = $auto_discover_name;
	}

	public function enqueue_active(): string {
		$script_args = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);
		$version     = $this->asset_resolver->get_version();

		foreach ( $this->js_handles as $js_handle => $is_active ) {
			if ( $is_active ) {
				$wp_handle   = self::get_wp_handle( $js_handle );
				$script_file = sprintf( 'js/front/%s.min.js', $js_handle );
				$script_url  = $this->get_asset_url( $script_file );

				wp_enqueue_script( $wp_handle, $script_url, array(), $version, $script_args );
			}
		}

		$css = '';

		$wp_filesystem = WP_Filesystem_Factory::get_wp_filesystem();

		foreach ( $this->css_handles as $css_handle => $is_active ) {
			if ( $is_active ) {
				$style_file   = sprintf( 'css/front/%s.min.css', $css_handle );
				$path_to_file = $this->get_asset_path( $style_file );

				$css .= (string) $wp_filesystem->get_contents( $path_to_file );
			}
		}

		return $css;
	}

	public function get_auto_discover_name(): string {
		return $this->auto_discover_name;
	}

	public function get_name(): string {
		return static::NAME;
	}
}
