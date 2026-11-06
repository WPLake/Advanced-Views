<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Plugin\Utils\Safe_Array_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
trait Markup_Field_Base {
	use Safe_Array_Arguments;

	/**
	 * @return Html_Node[]
	 */
	protected function compose_item_markup( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		if ( $markup_field_data->get_context()->get_field_settings()->has_external_layout() ) {
			return $this->compose_external_item_layout( $field_id, $item_id, $markup_field_data );
		}

		return $this->compose_internal_item_layout( $item_id, $markup_field_data );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_external_item_layout( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		return array();
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_internal_item_layout( string $item_id, Template_Field_Meta $markup_field_data ): array {
		return array();
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_item( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$item_outers = $markup_field_data->get_item_outers( $field_id, $item_id );
		$item_nodes  = $this->compose_item_markup( $field_id, $item_id, $markup_field_data );

		return $markup_field_data->wrap_with_item_outers( $item_outers, $item_nodes );
	}

	protected function get_field_class( string $suffix, Template_Field_Meta $markup_field_data ): string {
		$field_context = $markup_field_data->get_context();

		if ( Cpt_Settings::CLASS_GENERATION_NONE === $field_context->get_layout_settings()->classes_generation ) {
			return '';
		}

		$classes      = array();
		$is_first_tag = ! $markup_field_data->is_with_row_wrapper() &&
						! $markup_field_data->is_with_field_wrapper();

		if ( $is_first_tag ) {
			$classes[] = $field_context->get_layout_settings()->get_bem_name() . '__' . $field_context->get_field_settings()->id;

			if ( ! $field_context->get_layout_settings()->is_with_common_classes ) {
				return implode( ' ', $classes );
			}
		}

		$classes[] = $this->get_item_class( $suffix, $field_context->get_layout_settings(), $field_context->get_field_settings() );

		if ( ! $markup_field_data->is_with_field_wrapper() &&
			$field_context->get_layout_settings()->is_with_common_classes ) {
			$classes[] = $field_context->get_layout_settings()->get_bem_name() . '__field';
		}

		return implode( ' ', $classes );
	}

	// method is kept for backward compatibility, use the View->getItemClass() instead.
	protected function get_item_class( string $suffix, Layout_Settings $layout_settings, Field_Settings $field_settings ): string {
		return $layout_settings->get_item_class( $suffix, $field_settings );
	}

	public function is_empty_value_supported_in_markup(): bool {
		return false;
	}

	public function get_custom_field_wrapper_tag(): string {
		return '';
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		return array();
	}

	public function is_sub_fields_supported(): bool {
		return false;
	}

	public function get_front_assets( Field_Settings $field_settings ): array {
		return array();
	}
}
