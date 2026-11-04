<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row_Context;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Label_Builder {
	public static function compose_label( Row_Context $context ): Html_Tag {
		$token_factory = $context->get_token_factory();
		$field_id      = $context->get_field_id();
		$label_class   = self::resolve_classes( $context );
		$attributes    = array( 'class' => $label_class );
		$label_value   = $token_factory->variable( $field_id )
										->add_item_path( Markup_Field_Base::LABEL );

		return tag( 'p' )
			->attributes( $attributes )
			->content( $label_value );
	}

	protected static function resolve_classes( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$classes         = array();

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
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
}
