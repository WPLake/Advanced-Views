<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag\Plain_Tag_Printer;

function tag( string $name ): Html_Tag {
	return new Html_Tag( $name );
}

/**
 * @param Html_Tag|string|Htmlable|Closure ...$nodes
 */
function print_html( ...$nodes ): void {
	$renderer = new Html_Renderer( new Plain_Tag_Printer() );
	$renderer->print( ...$nodes );
}

/**
 * @param Html_Tag|string|Htmlable|Closure ...$nodes
 */
function render_html( ...$nodes ): string {
	$renderer = new Html_Renderer( new Plain_Tag_Printer() );

	return $renderer->render( ...$nodes );
}
