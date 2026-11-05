<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Naming;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;

final class Class_Names_Generator {
	public static function is_enabled( Layout_Settings $layout_settings ): bool {
		return Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation;
	}

	public static function field_name( Layout_Settings $layout_settings, Field_Settings $field_settings, string $row_suffix ): string {
		return sprintf(
			'%s__%s%s',
			$layout_settings->get_bem_name(),
			$field_settings->id,
			$row_suffix
		);
	}

	public static function label( Layout_Settings $layout_settings, Field_Settings $field_settings ): string {
		$classes = array();

		if ( self::is_enabled( $layout_settings ) ) {
			$bem_name = $layout_settings->get_bem_name();

			$classes[] = sprintf(
				'%s__%s-label',
				$bem_name,
				$field_settings->id
			);

			if ( $layout_settings->is_with_common_classes ) {
				$classes[] = sprintf( '%s__label', $bem_name );
			}
		}

		return join( ' ', $classes );
	}

	/**
	 * @param string $external_class A class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	public static function field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $row_suffix,
		bool $has_row_wrapper,
		string $external_class
	): string {
		$classes = array();

		if ( self::is_enabled( $layout_settings ) ) {
			$bem_name = $layout_settings->get_bem_name();

			$classes[] = $has_row_wrapper ?
				sprintf( '%s__%s-field', $bem_name, $field_settings->id ) :
				self::field_name( $layout_settings, $field_settings, $row_suffix );

			if ( $layout_settings->is_with_common_classes ) {
				$classes[] = sprintf( '%s__field', $bem_name );
			}
		}

		return self::join_with_external( $classes, $external_class );
	}

	/**
	 * @param string $external_class A class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	public static function row_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $row_suffix,
		string $row_type,
		string $external_class
	): string {
		$classes = array();

		if ( self::is_enabled( $layout_settings ) ) {
			$classes[] = self::field_name( $layout_settings, $field_settings, $row_suffix );

			if ( $layout_settings->is_with_common_classes ) {
				$classes[] = sprintf( '%s__%s', $layout_settings->get_bem_name(), $row_type );
			}
		}

		return self::join_with_external( $classes, $external_class );
	}

	/**
	 * @param string[] $classes
	 */
	protected static function join_with_external( array $classes, string $external_class ): string {
		if ( strlen( $external_class ) > 0 ) {
			$classes[] = $external_class;
		}

		return join( ' ', $classes );
	}
}
