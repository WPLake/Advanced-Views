<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern_Base;

class Light_Gallery_Pattern extends Template_Pattern_Base {
	const NAME = 'light-gallery';

	public function __construct( Asset_Resolver $asset_resolver, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $asset_resolver, $provider_cluster );

		$this->set_auto_discover_name( 'light-gallery' );
		$this->set_js_handles(
			array(
				'lightgallery' => false,
				'lg-thumbnail' => false,
			)
		);
		$this->set_css_handles(
			array(
				'lightgallery' => false,
				'lg-thumbnail' => false,
			)
		);
	}

	public function maybe_activate( Cpt_Settings $cpt_settings ): void {
		if ( $cpt_settings instanceof Layout_Settings &&
			! $this->is_enabled_js_handle( 'lightgallery' ) ) {
			$target_fields = $this->get_target_fields( $cpt_settings );

			if ( array() !== $target_fields ) {
				$this->enable_js_handle( 'lightgallery' );
				$this->enable_css_handle( 'lightgallery' );

				// this addon is always in use.
				$this->enable_js_handle( 'lg-thumbnail' );
				$this->enable_css_handle( 'lg-thumbnail' );
			}
		}
	}

	public function get_field_wrapper_tag( Field_Settings $field_settings, string $row_type ): string {
		return $field_settings->get_field_meta()->is_multiple() ?
			'ul' :
			'div';
	}

	/**
	 * @return array<string,string>
	 */
	public function get_field_wrapper_attrs( Field_Settings $field_settings, string $field_id ): array {
		return ! $field_settings->get_field_meta()->is_multiple() ?
			self::get_data_attrs( $field_id ) :
			array();
	}

	/**
	 * @return Html_Wrapper[]
	 */
	public function get_item_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $item_id
	): array {
		$data_attrs = self::get_data_attrs( $item_id );

		return array(
			new Html_Wrapper( 'li', $data_attrs ),
		);
	}

	public function enqueue_active(): string {
		$css_code = parent::enqueue_active();

		// font and image paths in CSS won't work, as CSS will be added right to the page,
		// replacing with the related installation path avoids it.
		$asset_url_base          = $this->get_asset_url( '' );
		$relative_asset_url_base = Plugin::make_url_relative( $asset_url_base );
		$relative_assets_url     = sprintf( 'url(%s', $relative_asset_url_base );

		return str_replace( 'url(../', $relative_assets_url, $css_code );
	}

	protected function print_js_code( string $var_name, Field_Settings $field_settings, Layout_Settings $layout_settings ): void {
		self::print_lightbox_js_code( $var_name, $field_settings, $layout_settings );
	}

	protected function print_css_code(
		string $field_selector,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
		self::print_light_box_css_code( $field_selector, $field_settings, $layout_settings );
	}

	protected static function print_lightbox_js_code(
		string $var_name,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
		$is_image = ! $field_settings->get_field_meta()->is_multiple();

		echo "\t/* https://www.lightgalleryjs.com/docs/settings/#lightgallery-core */\n";
		printf( "\tnew lightGallery(%s, {\n", esc_html( $var_name ) );
		if ( $is_image ) {
			echo "\t\tselector: 'this',\n";
		}
		echo "\t\tcloseOnTap: true,\n";
		printf( "\t\tcounter: %s,\n", esc_html( $is_image ? 'false' : 'true' ) );
		echo "\t\tdownload: false,\n";
		echo "\t\tallowMediaOverlap: false,\n";
		echo "\t\tenableDrag: false,\n";

		if ( ! $is_image ) {
			echo "\t\tplugins: [window.lgThumbnail,],\n";
		}

		echo "\t});";
	}

	protected static function print_light_box_css_code(
		string $field_selector,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
		if ( $field_settings->get_field_meta()->is_multiple() ) {
			printf(
				"%s {\n\tlist-style: none;\n}\n\n",
				esc_html( $field_selector )
			);
		}

		printf(
			"%s img:hover {\n\tcursor: zoom-in;\n}",
			esc_html( $field_selector )
		);
	}

	/**
	 * @param string $field_id
	 *
	 * @return array<string,string>
	 */
	protected static function get_data_attrs( string $field_id ): array {
		return array(
			'data-src'      => sprintf( '{{ %s.full_size }}', $field_id ),
			'data-sub-html' => sprintf( '{{ %s.caption }}', $field_id ),
		);
	}
}
