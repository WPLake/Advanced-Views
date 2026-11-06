<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Field_Context\Field_Context;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Inner_Row_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Row_Composer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Field_Builder {
	private Inner_Row_Policy $policy;

	public function __construct( Inner_Row_Policy $policy ) {
		$this->policy = $policy;
	}

	/**
	 * The field's own markup (or the custom one) inside the pattern outers and the field wrapper.
	 *
	 * @param Html_Node[]|null $custom_nodes
	 *
	 * @return Html_Node[]
	 */
	public function compose_field( Row_Context $context, Row_Composer $row_builder, ?array $custom_nodes = null ): array {
		$field_context = $context->with_row_type( 'field' );
		$field_nodes   = $custom_nodes ?? $this->compose_content( $field_context, $row_builder );

		$inner_nodes = Wrapper_Builder::wrap_inners( $context, $field_nodes );

		if ( Inner_Row_Policy::has_field_wrapper( $context ) ) {
			$is_inside_row_wrapper = $this->policy->has_row_wrapper( $context );

			return Wrapper_Builder::wrap_field(
				$context,
				$inner_nodes,
				$is_inside_row_wrapper
			);
		}

		return $inner_nodes;
	}

	/**
	 * @param Row_Context $context Must have the 'field' row type
	 *
	 * @return Html_Node[]
	 */
	public function compose_content( Row_Context $context, Row_Composer $row_builder ): array {
		$field_meta   = $context->get_field_meta();
		$is_existing  = $field_meta->is_field_exist();
		$markup_field = $context->get_markup_field();

		if ( $is_existing && $markup_field instanceof Markup_Field ) {
			$field_id          = $context->get_field_id();
			$markup_field_data = $this->create_field_meta( $context, $markup_field, $row_builder );

			return $markup_field->compose_html( $field_id, $markup_field_data );
		}

		return array();
	}

	protected function create_field_meta( Row_Context $context, Markup_Field $markup_field, Row_Composer $row_builder ): Template_Field_Meta {
		$layout_settings = $context->get_layout_settings();
		$item_settings   = $context->get_item_settings();
		$field_settings  = $context->get_field_settings();
		$field_meta      = $context->get_field_meta();
		$token_factory   = $context->get_token_factory();
		$patterns        = $context->get_pattern_container();

		$field_data_context = new Field_Context(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$markup_field
		);

		$markup_field_data = new Template_Field_Meta(
			$field_data_context,
			$row_builder,
			$token_factory
		);

		$field_assets      = $patterns->get_patterns();
		$has_field_wrapper = Inner_Row_Policy::has_field_wrapper( $context );
		$has_row_wrapper   = $this->policy->is_row_wrapper_required( $context );

		$markup_field_data->set_field_assets( $field_assets );
		$markup_field_data->set_is_with_field_wrapper( $has_field_wrapper );
		$markup_field_data->set_is_with_row_wrapper( $has_row_wrapper );

		return $markup_field_data;
	}
}
