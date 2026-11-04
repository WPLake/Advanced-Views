<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Field_Builder {
	private Row_Policy $policy;

	public function __construct( Row_Policy $policy ) {
		$this->policy = $policy;
	}

	/**
	 * @return Html_Node[]
	 */
	public function compose_field( Row_Context $context, Row_Builder $row_builder ): array {
		$field_meta   = $context->get_field_meta();
		$is_existing  = $field_meta->is_field_exist();
		$markup_field = $context->get_markup_field();

		if ( $is_existing && $markup_field instanceof Markup_Field ) {
			$field_context = $context
				->clone()
				->set_row_type( 'field' );
			$patterns      = $context->get_pattern_container();
			$field_id      = $context->get_field_id();

			$markup_field_data = new Template_Field_Data(
				$context->get_layout_settings(),
				$context->get_item_settings(),
				$context->get_field_settings(),
				$field_meta,
				$row_builder,
				$markup_field,
				$context->get_token_factory()
			);

			$field_assets      = $patterns->get_patterns();
			$has_field_wrapper = $this->policy->has_field_wrapper( $field_context );
			$has_row_wrapper   = $this->policy->is_row_wrapper_required( $field_context );

			$markup_field_data->set_field_assets( $field_assets );
			$markup_field_data->set_is_with_field_wrapper( $has_field_wrapper );
			$markup_field_data->set_is_with_row_wrapper( $has_row_wrapper );

			return $markup_field->compose_html( $field_id, $markup_field_data );
		}

		return array();
	}
}
