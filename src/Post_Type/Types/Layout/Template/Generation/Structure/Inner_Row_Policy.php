<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Structure;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

final class Inner_Row_Policy {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	public static function has_field_wrapper( Row_Context $context ): bool {
		$field_meta   = $context->get_field_meta();
		$is_existing  = $field_meta->is_field_exist();
		$markup_field = $context->get_markup_field();

		if ( $is_existing && $markup_field instanceof Markup_Field ) {
			$field_settings = $context->get_field_settings();
			$is_pattern_tag = self::resolve_pattern_field_wrapper_tag( $context ) !== '';

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

	public static function has_label( Row_Context $context ): bool {
		$field_settings = $context->get_field_settings();

		return strlen( $field_settings->label ) > 0;
	}

	/**
	 * A label inside the pattern's root wrapper (e.g. '.splide') breaks the library's work.
	 */
	public static function is_label_out_of_row( Row_Context $context ): bool {
		$patterns = $context->get_pattern_container();

		return $patterns->has_root_wrapper();
	}

	public static function resolve_row_wrapper_tag( Row_Context $context ): string {
		$pattern_tag = self::resolve_pattern_row_wrapper_tag( $context );
		$is_filled   = strlen( $pattern_tag ) > 0;

		return $is_filled ?
			$pattern_tag :
			'div';
	}

	public static function resolve_field_wrapper_tag( Row_Context $context ): string {
		$pattern_tag    = self::resolve_pattern_field_wrapper_tag( $context );
		$is_pattern_tag = strlen( $pattern_tag ) > 0;

		if ( $is_pattern_tag ) {
			return $pattern_tag;
		}

		$markup_field  = $context->get_markup_field();
		$custom_tag    = $markup_field instanceof Markup_Field ?
			$markup_field->get_custom_field_wrapper_tag() :
			'';
		$is_custom_tag = strlen( $custom_tag ) > 0;

		return $is_custom_tag ?
			$custom_tag :
			'div';
	}

	public function has_row_wrapper( Row_Context $context ): bool {
		$is_necessary = $this->is_row_wrapper_required( $context );

		$row_tag       = self::resolve_pattern_row_wrapper_tag( $context );
		$is_tag_filled = strlen( $row_tag ) > 0;

		return $is_necessary || $is_tag_filled;
	}

	public function is_row_wrapper_required( Row_Context $context ): bool {
		$field_meta     = $context->get_field_meta();
		$vendor_name    = $field_meta->get_vendor_name();
		$field_type     = $field_meta->get_type();
		$has_sub_fields = $this->provider_cluster->is_field_type_with_sub_fields( $vendor_name, $field_type );

		$has_label       = self::has_label( $context );
		$layout_settings = $context->get_layout_settings();

		return $layout_settings->is_with_unnecessary_wrappers || $has_label || $has_sub_fields;
	}

	protected static function resolve_pattern_row_wrapper_tag( Row_Context $context ): string {
		$patterns = $context->get_pattern_container();
		$wrapper  = $patterns->row_wrapper(
			$context->get_row_type(),
			$context->get_token_factory(),
			$context->get_field_id()
		);

		return $wrapper instanceof Html_Tag ?
			$wrapper->get_name() :
			'';
	}

	protected static function resolve_pattern_field_wrapper_tag( Row_Context $context ): string {
		$patterns = $context->get_pattern_container();
		$wrapper  = $patterns->field_wrapper(
			$context->get_row_type(),
			$context->get_token_factory(),
			$context->get_field_id()
		);

		return $wrapper instanceof Html_Tag ?
			$wrapper->get_name() :
			'';
	}
}
