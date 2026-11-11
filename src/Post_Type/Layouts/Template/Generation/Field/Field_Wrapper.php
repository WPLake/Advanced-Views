<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Structure\Inner_Row_Policy;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Outers_Builder;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Field_Wrapper {
	/**
	 * @param Html_Node[] $nodes
	 *
	 * @return Html_Node[]
	 */
	public static function wrap_inners( Row_Context $context, array $nodes ): array {
		$patterns        = $context->get_pattern_container();
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$field_id        = $context->get_field_id();
		$row_type        = $context->get_row_type();
		$token_factory   = $context->get_token_factory();

		$field_outers = $patterns->field_outers(
			$layout_settings,
			$field_settings,
			$field_id,
			$row_type,
			$token_factory
		);

		return Outers_Builder::compose_outers(
			$field_outers,
			$nodes
		);
	}

	/**
	 * @param Html_Node[] $nodes
	 *
	 * @return Html_Node[]
	 */
	public static function wrap_field( Row_Context $context, array $nodes, bool $is_inside_row_wrapper ): array {
		$wrapper     = self::compose_wrapper( $context, $is_inside_row_wrapper );
		$wrapper_tag = $wrapper->content( ...$nodes );

		return array( $wrapper_tag );
	}

	public static function resolve_field_name( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$bem_name        = $layout_settings->get_bem_name();
		$row_suffix      = $context->get_row_suffix();

		return sprintf(
			'%s__%s%s',
			$bem_name,
			$field_settings->id,
			$row_suffix
		);
	}

	/**
	 * @param string[] $classes
	 */
	public static function get_filled_classes( array $classes ): string {
		$filled_classes = array_filter(
			$classes,
			static fn( string $class_name ): bool => strlen( $class_name ) > 0
		);

		return join( ' ', $filled_classes );
	}

	protected static function compose_wrapper( Row_Context $context, bool $is_inside_row_wrapper ): Html_Tag {
		$field_settings = $context->get_field_settings();
		$field_id       = $context->get_field_id();
		$patterns       = $context->get_pattern_container();

		$tag = Inner_Row_Policy::resolve_field_wrapper_tag( $context );

		$attrs = $patterns->field_wrapper_attrs( $field_settings, $field_id );
		unset( $attrs['class'] );

		$classes     = self::generate_class( $context, $is_inside_row_wrapper );
		$class_attrs = array( 'class' => $classes );
		$all_attrs   = array_merge( $class_attrs, $attrs );

		return tag( $tag )
			->attributes( $all_attrs );
	}

	/**
	 * The class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	protected static function generate_class( Row_Context $context, bool $is_inside_row_wrapper ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$patterns        = $context->get_pattern_container();

		$field_id       = $context->get_field_id();
		$pattern_attrs  = $patterns->field_wrapper_attrs( $field_settings, $field_id );
		$external_class = string( $pattern_attrs, 'class' );
		$is_enabled     = $layout_settings->is_classes_generation_enabled();
		$classes        = $is_enabled ?
			self::resolve_classes( $context, $is_inside_row_wrapper ) :
			array();

		return self::get_filled_classes( array( ...$classes, $external_class ) );
	}

	/**
	 * @return string[]
	 */
	protected static function resolve_classes( Row_Context $context, bool $is_inside_row_wrapper ): array {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$bem_name        = $layout_settings->get_bem_name();

		$row_wrapper_class = sprintf(
			'%s__%s-field',
			$bem_name,
			$field_settings->id
		);
		$field_name_class  = self::resolve_field_name( $context );

		$classes = array(
			$is_inside_row_wrapper ?
				$row_wrapper_class :
				$field_name_class,
		);

		if ( $layout_settings->is_with_common_classes ) {
			$classes[] = sprintf( '%s__field', $bem_name );
		}

		return $classes;
	}
}
