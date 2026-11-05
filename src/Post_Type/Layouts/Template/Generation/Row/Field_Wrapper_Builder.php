<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Policy\Inner_Row_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Field_Wrapper_Builder {
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
		if ( $this->policy->has_field_wrapper( $context ) ) {
			$has_row_wrapper = $this->policy->has_row_wrapper( $context );
			$wrapper         = $this->compose_wrapper( $context, $has_row_wrapper );

			return array( $wrapper->content( ...$nodes ) );
		}

		return $nodes;
	}

	protected function compose_wrapper( Row_Context $context, bool $has_row_wrapper ): Html_Tag {
		$field_settings = $context->get_field_settings();
		$field_id       = $context->get_field_id();
		$patterns       = $context->get_pattern_container();

		$attrs = $patterns->field_wrapper_attrs( $field_settings, $field_id );
		unset( $attrs['class'] );

		$classes = $this->generate_class( $context, $has_row_wrapper );
		$tag     = $this->policy->field_wrapper_tag( $context );

		$class_attrs = array( 'class' => $classes );
		$all_attrs   = array_merge( $class_attrs, $attrs );

		return tag( $tag )
			->attributes( $all_attrs );
	}

	/**
	 * The class from a pattern, e.g. 'splide', is kept even if the generation is off, as JS requires it
	 */
	protected function generate_class( Row_Context $context, bool $has_row_wrapper ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$patterns        = $context->get_pattern_container();

		$pattern_attrs  = $patterns->field_wrapper_attrs( $field_settings, $context->get_field_id() );
		$external_class = string( $pattern_attrs, 'class' );
		$classes        = $layout_settings->is_classes_generation_enabled() ?
			$this->resolve_classes( $context, $has_row_wrapper ) :
			array();

		return Row_Class_Names::join_with_external( $classes, $external_class );
	}

	/**
	 * @return string[]
	 */
	protected function resolve_classes( Row_Context $context, bool $has_row_wrapper ): array {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$bem_name        = $layout_settings->get_bem_name();

		$row_wrapper_class = sprintf(
			'%s__%s-field',
			$bem_name,
			$field_settings->id
		);
		$field_name_class  = Row_Class_Names::field_name( $context );

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
}
