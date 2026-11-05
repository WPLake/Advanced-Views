<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Inner_Row_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Row_Wrapper_Builder {
	private Inner_Row_Policy $policy;

	public function __construct( Inner_Row_Policy $policy ) {
		$this->policy = $policy;
	}

	/**
	 * @param Html_Node[] $nodes
	 *
	 * @return Html_Node[]
	 */
	public function wrap( Row_Context $context, array $nodes ): array {
		if ( $this->policy->has_row_wrapper( $context ) ) {
			$wrapper = self::compose_wrapper( $context );

			return array( $wrapper->content( ...$nodes ) );
		}

		return $nodes;
	}

	protected static function compose_wrapper( Row_Context $context ): Html_Tag {
		$classes = self::generate_class( $context );
		$tag     = Inner_Row_Policy::resolve_row_wrapper_tag( $context );

		$attributes = array( 'class' => $classes );

		return tag( $tag )
			->attributes( $attributes );
	}

	/**
	 * The class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	protected static function generate_class( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$patterns        = $context->get_pattern_container();

		$row_type       = $context->get_row_type();
		$external_class = $patterns->row_wrapper_class( $row_type );
		$classes        = $layout_settings->is_classes_generation_enabled() ?
			self::resolve_classes( $context ) :
			array();

		return Field_Wrapper_Builder::get_filled_classes( array( ...$classes, $external_class ) );
	}

	/**
	 * @return string[]
	 */
	protected static function resolve_classes( Row_Context $context ): array {
		$layout_settings = $context->get_layout_settings();
		$classes         = array( Field_Wrapper_Builder::resolve_field_name( $context ) );

		if ( $layout_settings->is_with_common_classes ) {
			$bem_name = $layout_settings->get_bem_name();
			$row_type = $context->get_row_type();

			$classes[] = sprintf( '%s__%s', $bem_name, $row_type );
		}

		return $classes;
	}
}
