<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Label_Renderer {
	public static function get_tag(
		Token_Factory $token_factory,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		int &$tabs_number,
		string $field_id
	): Html_Tag {
		$label_class = self::resolve_classes( $layout_settings, $field_settings );

		return tag( 'p' )
			->attributes( array( 'class' => $label_class ) )
			->content(
				fn() => $token_factory->formatter()
										->new_line()
										->tab( $tabs_number + 1 ),
				$token_factory->variable( $field_id )
								->add_item_path( Markup_Field_Base::LABEL ),
				fn() => $token_factory->formatter()
										->new_line()
										->tab( $tabs_number )
			);
	}

	protected static function resolve_classes( Layout_Settings $layout_settings, Field_Settings $field_settings ): string {
		$classes = array();

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$bem_name  = $layout_settings->get_bem_name();
			$classes[] = sprintf( '%s__%s-label', $bem_name, $field_settings->id );

			if ( $layout_settings->is_with_common_classes ) {
				$classes[] = sprintf( '%s__label', $bem_name );
			}
		}

		return join( ' ', $classes );
	}
}
