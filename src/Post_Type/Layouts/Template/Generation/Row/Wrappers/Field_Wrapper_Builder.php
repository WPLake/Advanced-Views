<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Wrappers;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Field_Wrapper_Builder {
	/**
	 * @param array<string,mixed> $attrs Without the class, it's the $classes
	 */
	public static function compose_wrapper( string $tag, string $classes, array $attrs ): Html_Tag {
		$class_attrs = array( 'class' => $classes );
		$all_attrs   = array_merge( $class_attrs, $attrs );

		return tag( $tag )
			->attributes( $all_attrs );
	}
}
