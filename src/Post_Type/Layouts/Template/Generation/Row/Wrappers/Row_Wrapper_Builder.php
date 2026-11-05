<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Wrappers;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Row_Wrapper_Builder {
	public static function compose_wrapper( string $tag, string $classes ): Html_Tag {
		$attributes = array( 'class' => $classes );

		return tag( $tag )
			->attributes( $attributes );
	}
}
