<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Field_Context\Field_Context;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;

final class Field_Value_Resolver {
	/**
	 * @param mixed $value
	 * @param mixed $formatted_value In repeater, formatted value must be passed directly
	 *
	 * @return mixed[]
	 */
	public function resolve_value(
		Field_Context $field_context,
		Layout $layout,
		Source $source,
		$value,
		bool $is_for_validation = false,
		$formatted_value = null
	): array {
		$field_value = new Raw_Field_Value(
			$field_context,
			$layout,
			$source,
			$this
		);

		$field_value->set_value( $value );

		if ( null !== $formatted_value ) {
			$field_value->set_formatted_value( $formatted_value );
		}

		$field_data = ! $is_for_validation ?
			$field_context->get_markup_field()->get_template_variables( $field_value ) :
			$field_context->get_markup_field()->get_validation_template_variables( $field_value );

		$layout_settings = $field_context->get_layout_settings();
		$short_unique_id = $layout_settings->get_unique_id( true );

		return $this->apply_field_data_filter( $field_data, $field_context->get_field_meta(), $short_unique_id );
	}

	/**
	 * @param array<string,mixed> $field_data
	 *
	 * @return array<string|int,mixed>
	 */
	protected function apply_field_data_filter(
		array $field_data,
		Field_Meta $field_meta,
		string $short_unique_view_id
	): array {
		$field_type = $field_meta->get_type();
		$field_name = $field_meta->get_name();

		$field_data = Plugin::apply_filters(
			array(
				'advanced_views/layout/field_data',
				'acf_views/view/field_data',
			),
			$field_data,
			$field_meta,
			$short_unique_view_id
		);

		if ( $field_meta->is_native_type() ) {
			$field_data = Plugin::apply_filters(
				array(
					sprintf( 'advanced_views/layout/field_data/type=%s', $field_type ),
					sprintf( 'acf_views/view/field_data/type=%s', $field_type ),
				),
				$field_data,
				$field_meta,
				$short_unique_view_id
			);
		}

		$field_data = Plugin::apply_filters(
			array(
				sprintf( 'advanced_views/layout/field_data/name=%s', $field_name ),
				sprintf( 'acf_views/view/field_data/name=%s', $field_name ),
			),
			$field_data,
			$field_meta,
			$short_unique_view_id
		);

		$field_data = Plugin::apply_filters(
			array(
				sprintf( 'advanced_views/layout/field_data/layout_id=%s', $short_unique_view_id ),
				sprintf( 'acf_views/view/field_data/view_id=%s', $short_unique_view_id ),
			),
			$field_data,
			$field_meta,
			$short_unique_view_id
		);

		return arr( $field_data );
	}
}
