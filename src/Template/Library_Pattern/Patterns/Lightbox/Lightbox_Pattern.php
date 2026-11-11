<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Lightbox;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder\JS_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Css\Builder\CSS_Pattern_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\Layout_Pattern_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

class Lightbox_Pattern extends Layout_Pattern_Base {
	const NAME = 'acf-views-lightbox';

	/**
	 * @var array<string,array<string,mixed>>
	 */
	private array $light_boxes;
	private JS_Library $js_library;
	private CSS_Library $css_library;

	public function __construct( Field_Provider_Cluster $provider_cluster, JS_Pattern_Builder $js_pattern_builder, CSS_Pattern_Builder $css_pattern_builder ) {
		parent::__construct( $provider_cluster, $js_pattern_builder, $css_pattern_builder );

		$this->set_auto_discover_name( 'acf-views-lightbox' );

		$this->light_boxes = array();
		$this->js_library  = new JS_Library();
		$this->css_library = new CSS_Library();
	}

	public function get_js_library(): JS_Library {
		return $this->js_library;
	}

	public function get_css_library(): CSS_Library {
		return $this->css_library;
	}

	protected static function get_field_prefix( Layout_Settings $layout_settings, Field_Settings $field_settings ): string {
		$bem_name     = $layout_settings->get_bem_name();
		$field_prefix = sprintf( '%s__', $bem_name );

		if ( ! $layout_settings->is_with_common_classes ) {
			$field_prefix .= sprintf( '%s-', $field_settings->id );
		}

		return $field_prefix;
	}

	protected function add_light_box( Layout_Settings $layout_settings, Field_Settings $field_settings ): void {
		$is_gallery    = $field_settings->get_field_meta()->is_multiple();
		$item_selector = $this->get_item_selector( $layout_settings, $field_settings, true, false );
		$bem_name      = $layout_settings->get_bem_name();
		$field_prefix  = self::get_field_prefix( $layout_settings, $field_settings );

		$light_box = array(
			'selector'    => $item_selector,
			'bemName'     => $bem_name,
			'fieldPrefix' => $field_prefix,
			'isGallery'   => $is_gallery,
		);

		/**
		 * Selector as a key ensures that we've no duplicates -
		 * even if the same Layout->lightboxField appears several times on the page -
		 * as we need only list of distinct elements.
		 */
		$this->light_boxes[ $item_selector ] = $light_box;
	}

	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return $field_settings->get_field_meta()->is_multiple() ?
			Gallery_Lightbox_CSS::class :
			Image_Lightbox_CSS::class;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields = $this->get_target_fields( $layout_settings );

		if ( array() !== $target_fields ) {
			foreach ( $target_fields as $target_field ) {
				$this->add_light_box( $layout_settings, $target_field );
			}

			$light_boxes = array_values( $this->light_boxes );

			$this->js_library->enable();
			$this->js_library->set_light_boxes( $light_boxes );
			$this->css_library->enable();
		}
	}

	public function get_field_wrapper_tag( Field_Settings $field_settings, string $row_type ): string {
		return $field_settings->get_field_meta()->is_multiple() ?
			'ul' :
			'div';
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
		return array(
			tag( 'li' ),
		);
	}

	public function get_inner_variable_attributes( Field_Settings $field_settings, string $field_id ): array {
		return array(
			'data-full-size' => array(
				'field_id' => $field_id,
				'item_key' => 'full_size',
			),
		);
	}
}
