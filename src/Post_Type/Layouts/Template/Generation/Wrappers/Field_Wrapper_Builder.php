<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Field_Wrapper_Builder {
	public static function compose_wrapper( Row_Context $context, bool $has_row_wrapper ): Html_Tag {
		$patterns       = $context->get_pattern_container();
		$field_settings = $context->get_field_settings();
		$field_id       = $context->get_field_id();
		$attrs          = $patterns->field_wrapper_attrs( $field_settings, $field_id );
		$attr_class     = $attrs['class'] ?? '';
		unset( $attrs['class'] );

		$tag         = self::resolve_tag( $context );
		$classes     = self::resolve_classes( $context, $has_row_wrapper, $attr_class );
		$class_attrs = array( 'class' => $classes );
		$all_attrs   = array_merge( $class_attrs, $attrs );

		return tag( $tag )
			->attributes( $all_attrs );
	}

	protected static function resolve_tag( Row_Context $context ): string {
		$patterns       = $context->get_pattern_container();
		$field_settings = $context->get_field_settings();
		$row_type       = $context->get_row_type();
		$pattern_tag    = $patterns->field_wrapper_tag( $field_settings, $row_type );
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

	protected static function resolve_classes( Row_Context $context, bool $has_row_wrapper, string $attr_class ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$classes         = array();

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$bem_name = $layout_settings->get_bem_name();

			$classes[] = $has_row_wrapper ?
				sprintf( '%s__%s-field', $bem_name, $field_settings->id ) :
				$context->get_field_name_class();

			if ( $layout_settings->is_with_common_classes ) {
				$classes[] = sprintf( '%s__field', $bem_name );
			}
		}

		// do not consider classes_generation=none, as external classes, e.g. 'splide', are required for js.
		$is_filled = strlen( $attr_class ) > 0;

		if ( $is_filled ) {
			$classes[] = $attr_class;
		}

		return join( ' ', $classes );
	}
}
