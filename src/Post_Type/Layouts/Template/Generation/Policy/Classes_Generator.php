<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Policy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Composite_Attribute;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\compose_attribute;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

final class Classes_Generator {
	public static function generate_root_class( Token_Factory $token_factory, Layout_Settings $layout_settings ): Composite_Attribute {
		$parts = self::is_enabled( $layout_settings ) ?
			self::resolve_root_parts( $token_factory, $layout_settings ) :
			array();

		return compose_attribute( $parts );
	}

	public static function is_enabled( Layout_Settings $layout_settings ): bool {
		return Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation;
	}

	public static function generate_field_name_class( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$bem_name        = $layout_settings->get_bem_name();

		return sprintf(
			'%s__%s%s',
			$bem_name,
			$field_settings->id,
			$context->get_row_suffix()
		);
	}

	public static function generate_label_class( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$classes         = self::is_enabled( $layout_settings ) ?
			self::resolve_label_classes( $context ) :
			array();

		return join( ' ', $classes );
	}

	/**
	 * The class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	public static function generate_field_wrapper_class( Row_Context $context, bool $has_row_wrapper ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$patterns        = $context->get_pattern_container();

		$pattern_attrs  = $patterns->field_wrapper_attrs( $field_settings, $context->get_field_id() );
		$external_class = string( $pattern_attrs, 'class' );
		$classes        = self::is_enabled( $layout_settings ) ?
			self::resolve_field_classes( $context, $has_row_wrapper ) :
			array();

		return self::join_with_external( $classes, $external_class );
	}

	/**
	 * The class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	public static function generate_row_wrapper_class( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$patterns        = $context->get_pattern_container();

		$external_class = $patterns->row_wrapper_class( $context->get_row_type() );
		$classes        = self::is_enabled( $layout_settings ) ?
			self::resolve_row_classes( $context ) :
			array();

		return self::join_with_external( $classes, $external_class );
	}

	/**
	 * @return array<int,string|Variable_Token>
	 */
	protected static function resolve_root_parts( Token_Factory $token_factory, Layout_Settings $layout_settings ): array {
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

		return $parts;
	}

	/**
	 * @return string[]
	 */
	protected static function resolve_label_classes( Row_Context $context ): array {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$bem_name        = $layout_settings->get_bem_name();

		$classes = array(
			sprintf(
				'%s__%s-label',
				$bem_name,
				$field_settings->id
			),
		);

		if ( $layout_settings->is_with_common_classes ) {
			$classes[] = sprintf( '%s__label', $bem_name );
		}

		return $classes;
	}

	/**
	 * @return string[]
	 */
	protected static function resolve_row_classes( Row_Context $context ): array {
		$layout_settings = $context->get_layout_settings();
		$classes         = array( self::generate_field_name_class( $context ) );

		if ( $layout_settings->is_with_common_classes ) {
			$bem_name = $layout_settings->get_bem_name();

			$classes[] = sprintf( '%s__%s', $bem_name, $context->get_row_type() );
		}

		return $classes;
	}

	/**
	 * @return string[]
	 */
	protected static function resolve_field_classes( Row_Context $context, bool $has_row_wrapper ): array {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$bem_name        = $layout_settings->get_bem_name();

		$row_wrapper_class = sprintf(
			'%s__%s-field',
			$bem_name,
			$field_settings->id
		);
		$field_name_class  = self::generate_field_name_class( $context );

		$classes = array(
			$has_row_wrapper ?
				$row_wrapper_class :
				$field_name_class,
		);

		if ( $layout_settings->is_with_common_classes ) {
			$classes[] = sprintf( '%s__field', $bem_name );
		}

		return $classes;
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
