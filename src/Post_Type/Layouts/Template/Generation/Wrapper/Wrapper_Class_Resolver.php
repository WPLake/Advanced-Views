<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;

final class Wrapper_Class_Resolver {
	public static function resolve_row_classes(
		Layout_Settings $layout_settings,
		string $field_name_class,
		string $type,
		string $row_class
	): string {
		$classes = array();

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$classes[] = $field_name_class;

			if ( $layout_settings->is_with_common_classes ) {
				$bem_name  = $layout_settings->get_bem_name();
				$classes[] = sprintf( '%s__%s', $bem_name, $type );
			}
		}

		// do not consider classes_generation=none, as external classes, e.g. 'splide', are required for js.
		$classes[] = $row_class;

		return self::join_classes( $classes );
	}

	public static function resolve_field_classes(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		bool $is_with_row_wrapper,
		string $field_name_class,
		string $attr_class
	): string {
		$classes = array();

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$bem_name = $layout_settings->get_bem_name();

			$classes[] = $is_with_row_wrapper ?
				sprintf( '%s__%s-field', $bem_name, $field_settings->id ) :
				$field_name_class;

			if ( $layout_settings->is_with_common_classes ) {
				$classes[] = sprintf( '%s__field', $bem_name );
			}
		}

		// do not consider classes_generation=none, as external classes, e.g. 'splide', are required for js.
		$classes[] = $attr_class;

		return self::join_classes( $classes );
	}

	/**
	 * @param string[] $classes
	 */
	protected static function join_classes( array $classes ): string {
		$filter = static fn( string $class ): bool => '' !== $class;
		$non_empty = array_filter( $classes, $filter );

		return join( ' ', $non_empty );
	}
}
