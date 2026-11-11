<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Light_Gallery;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder\JS_Pattern_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\Layout_Pattern_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

class Light_Gallery_Pattern extends Layout_Pattern_Base {
	const NAME = 'light-gallery';

	private JS_Library $js_library;
	private CSS_Library $css_library;

	public function __construct( Field_Provider_Cluster $provider_cluster, JS_Pattern_Builder $js_pattern_builder ) {
		parent::__construct( $provider_cluster, $js_pattern_builder );

		$this->set_auto_discover_name( 'light-gallery' );

		$this->js_library  = new JS_Library();
		$this->css_library = new CSS_Library();
	}

	public function get_js_library(): JS_Library {
		return $this->js_library;
	}

	public function get_css_library(): CSS_Library {
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

	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return $field_settings->get_field_meta()->is_multiple() ?
			Lightbox_Multi_JS::class :
			Lightbox_Single_JS::class;
	}

	protected function print_css_code(
		string $field_selector,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
		self::print_light_box_css_code( $field_selector, $field_settings, $layout_settings );
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
