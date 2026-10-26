<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Tag_Printer\Plain_Tag_Printer;

function tag( string $name ): Html_Tag {
	return new Html_Tag( $name );
}

/**
 * @param Html_Tag|string|Htmlable|Closure ...$nodes
 */
function print_html( ...$nodes ): void {
	$tag_printer = new Plain_Tag_Printer();

	$renderer = new Html_Renderer( $tag_printer );
	$renderer->print( ...$nodes );
}

/**
 * @param Html_Tag|string|Htmlable|Closure ...$nodes
 */
function render_html( ...$nodes ): string {
	$tag_printer = new Plain_Tag_Printer();

	$renderer = new Html_Renderer( $tag_printer );
	return $renderer->render( ...$nodes );
}
