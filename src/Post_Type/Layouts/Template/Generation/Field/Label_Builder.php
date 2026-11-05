<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Label_Builder {
	public static function compose_label( Token_Factory $token_factory, string $field_id, string $classes ): Html_Tag {
		$attributes  = array( 'class' => $classes );
		$label_value = $token_factory->variable( $field_id )
										->add_item_path( Markup_Field_Base::LABEL );

		return tag( 'p' )
			->attributes( $attributes )
			->content( $label_value );
	}
}
