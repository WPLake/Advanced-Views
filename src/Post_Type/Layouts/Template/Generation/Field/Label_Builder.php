<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Label_Builder {
	public static function compose_label( Row_Context $context ): Html_Tag {
		$classes       = self::resolve_classes( $context );
		$attributes    = array( 'class' => $classes );
		$field_id      = $context->get_field_id();
		$token_factory = $context->get_token_factory();
		$label_value   = $token_factory
			->variable( $field_id )
			->add_item_path( Markup_Field::LABEL );

		return tag( 'p' )
			->attributes( $attributes )
			->content( $label_value );
	}

	protected static function resolve_classes( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$classes         = $layout_settings->is_classes_generation_enabled() ?
			self::resolve_enabled_classes( $context ) :
			array();

		return join( ' ', $classes );
	}

	/**
	 * @return string[]
	 */
	protected static function resolve_enabled_classes( Row_Context $context ): array {
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
}
