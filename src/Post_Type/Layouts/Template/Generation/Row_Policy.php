<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;

final class Row_Policy {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	public function has_row_wrapper( Row_Context $context ): bool {
		$is_necessary = $this->is_row_wrapper_required( $context );

		$patterns       = $context->get_pattern_container();
		$field_settings = $context->get_field_settings();
		$row_type       = $context->get_row_type();
		$row_tag        = $patterns->row_wrapper_tag( $field_settings, $row_type );
		$is_tag_filled  = strlen( $row_tag ) > 0;

		return $is_necessary || $is_tag_filled;
	}

	/**
	 * Without the wrapper required by the patterns.
	 */
	public function is_row_wrapper_required( Row_Context $context ): bool {
		$field_meta     = $context->get_field_meta();
		$vendor_name    = $field_meta->get_vendor_name();
		$field_type     = $field_meta->get_type();
		$has_sub_fields = $this->provider_cluster->is_field_type_with_sub_fields( $vendor_name, $field_type );

		$has_label       = $this->has_label( $context );
		$layout_settings = $context->get_layout_settings();

		return $layout_settings->is_with_unnecessary_wrappers || $has_label || $has_sub_fields;
	}

	public function has_field_wrapper( Row_Context $context ): bool {
		$field_meta   = $context->get_field_meta();
		$is_existing  = $field_meta->is_field_exist();
		$markup_field = $context->get_markup_field();

		if ( $is_existing && $markup_field instanceof Markup_Field ) {
			$patterns       = $context->get_pattern_container();
			$field_settings = $context->get_field_settings();
			$row_type       = $context->get_row_type();
			$pattern_tag    = $patterns->field_wrapper_tag( $field_settings, $row_type );
			$is_pattern_tag = strlen( $pattern_tag ) > 0;

			$custom_tag    = $markup_field->get_custom_field_wrapper_tag();
			$is_custom_tag = strlen( $custom_tag ) > 0;

			$layout_settings = $context->get_layout_settings();
			$is_with_wrapper = $markup_field->is_with_field_wrapper(
				$layout_settings,
				$field_settings,
				$field_meta
			);

			return $is_pattern_tag || $is_custom_tag || $is_with_wrapper;
		}

		return $is_existing;
	}

	public function has_label( Row_Context $context ): bool {
		$field_settings = $context->get_field_settings();

		return strlen( $field_settings->label ) > 0;
	}

	public function is_label_out_of_row( Row_Context $context ): bool {
		$patterns = $context->get_pattern_container();

		return $patterns->is_label_out_of_row();
	}
}
