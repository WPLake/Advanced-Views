<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Policy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Composite_Attribute;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use function Org\Wplake\Advanced_Views\Template\Html\compose_attribute;

final class Classes_Generator {
	public static function root( Token_Factory $token_factory, Layout_Settings $layout_settings ): Composite_Attribute {
		if ( self::is_enabled( $layout_settings ) ) {
			$bem_name      = $layout_settings->get_bem_name();
			$variable_name = Hard_Layout_Cpt::variable_name();
			$parts         = array(
				$token_factory
					->variable( $variable_name )
					->add_item_path( 'classes' ),
				$bem_name,
			);

			// not necessary if the bemName is defined.
			$is_id_required = ! $layout_settings->has_unique_bem_name();

			if ( $is_id_required ) {
				$parts[] = sprintf( ' %s--id--', $bem_name );
				$parts[] = $token_factory
					->variable( $variable_name )
					->add_item_path( 'id' );
			}

			$parts[] = sprintf( ' %s--object-id--', $bem_name );
			$parts[] = $token_factory
				->variable( $variable_name )
				->add_item_path( 'object_id' );

			return compose_attribute( $parts );
		}

		return compose_attribute( array() );
	}

	public static function is_enabled( Layout_Settings $layout_settings ): bool {
		return Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation;
	}

	public static function field_name( Layout_Settings $layout_settings, Field_Settings $field_settings, string $row_suffix ): string {
		$bem_name = $layout_settings->get_bem_name();

		return sprintf(
			'%s__%s%s',
			$bem_name,
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

			$row_wrapper_class = sprintf(
				'%s__%s-field',
				$bem_name,
				$field_settings->id
			);
			$field_name_class  = self::field_name( $layout_settings, $field_settings, $row_suffix );

			$classes[] = $has_row_wrapper ?
				$row_wrapper_class :
				$field_name_class;

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
				$bem_name = $layout_settings->get_bem_name();

				$classes[] = sprintf( '%s__%s', $bem_name, $row_type );
			}
		}

		return self::join_with_external( $classes, $external_class );
	}

	/**
	 * @param string[] $classes
	 */
	protected static function join_with_external( array $classes, string $external_class ): string {
		$is_filled = strlen( $external_class ) > 0;

		if ( $is_filled ) {
			$classes[] = $external_class;
		}

		return join( ' ', $classes );
	}
}
