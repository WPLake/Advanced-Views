<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Light_Gallery;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\CSS_Library\CSS_Library;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library\JS_Library;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\Layout_Pattern_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

class Light_Gallery_Pattern extends Layout_Pattern_Base {
	const NAME = 'light-gallery';

	private Light_Gallery_JS $js_library;
	private Light_Gallery_CSS $css_library;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $provider_cluster );

		$this->set_auto_discover_name( 'light-gallery' );

		$this->js_library  = new Light_Gallery_JS();
		$this->css_library = new Light_Gallery_CSS();
	}

	public function get_js_library(): ?JS_Library {
		return $this->js_library;
	}

	public function get_css_library(): ?CSS_Library {
		return $this->css_library;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		if ( $this->js_library->has_active_js() ) {
			return;
		}

		$target_fields = $this->get_target_fields( $layout_settings );

		if ( array() !== $target_fields ) {
			$this->js_library->enable();
			$this->css_library->enable();
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
	 * @return Html_Tag[]
	 */
	public function get_item_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $item_id,
		Token_Factory $token_factory
	): array {
		$data_attrs = self::get_data_attrs( $item_id );

		return array(
			tag( 'li' )
				->attributes( $data_attrs ),
		);
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
