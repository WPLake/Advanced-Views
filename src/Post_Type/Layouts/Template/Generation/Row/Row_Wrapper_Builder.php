<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Policy\Inner_Row_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Row_Wrapper_Builder {
	private Inner_Row_Policy $policy;

	public function __construct( Inner_Row_Policy $policy ) {
		$this->policy = $policy;
	}

	public function compose( Row_Context $context ): Html_Tag {
		$classes = $this->generate_class( $context );
		$tag     = $this->policy->row_wrapper_tag( $context );

		$attributes = array( 'class' => $classes );

		return tag( $tag )
			->attributes( $attributes );
	}

	/**
	 * The class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	protected function generate_class( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$patterns        = $context->get_pattern_container();

		$external_class = $patterns->row_wrapper_class( $context->get_row_type() );
		$classes        = $layout_settings->is_classes_generation_enabled() ?
			$this->resolve_classes( $context ) :
			array();

		return Row_Class_Names::join_with_external( $classes, $external_class );
	}

	/**
	 * @return string[]
	 */
	protected function resolve_classes( Row_Context $context ): array {
		$layout_settings = $context->get_layout_settings();
		$classes         = array( Row_Class_Names::field_name( $context ) );

		if ( $layout_settings->is_with_common_classes ) {
			$bem_name = $layout_settings->get_bem_name();

			$classes[] = sprintf( '%s__%s', $bem_name, $context->get_row_type() );
		}

		return $classes;
	}
}
