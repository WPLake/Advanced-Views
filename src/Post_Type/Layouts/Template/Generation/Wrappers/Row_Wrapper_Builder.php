<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Row_Wrapper_Builder {
	public static function compose_wrapper( Row_Context $context ): Html_Tag {
		$patterns       = $context->get_pattern_container();
		$field_settings = $context->get_field_settings();
		$row_type       = $context->get_row_type();
		$tag            = $patterns->row_wrapper_tag( $field_settings, $row_type );
		$is_filled      = strlen( $tag ) > 0;
		$tag            = $is_filled ?
			$tag :
			'div';
		$classes        = self::resolve_classes( $context );
		$attributes     = array( 'class' => $classes );

		return tag( $tag )
			->attributes( $attributes );
	}

	protected static function resolve_classes( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$row_type        = $context->get_row_type();
		$classes         = array();

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$bem_name  = $layout_settings->get_bem_name();
			$classes[] = $context->get_field_name_class();

			if ( $layout_settings->is_with_common_classes ) {
				$classes[] = sprintf( '%s__%s', $bem_name, $row_type );
			}
		}

		// do not consider classes_generation=none, as external classes, e.g. 'splide', are required for js.
		$patterns      = $context->get_pattern_container();
		$pattern_class = $patterns->row_wrapper_class( $row_type );
		$is_filled     = strlen( $pattern_class ) > 0;

		if ( $is_filled ) {
			$classes[] = $pattern_class;
		}

		return join( ' ', $classes );
	}
}
