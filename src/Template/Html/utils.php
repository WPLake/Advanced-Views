<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

function tag( string $name ): Html_Tag {
	return new Html_Tag( $name );
}

/**
 * @param Html_Tag|string|Htmlable|Closure[] $nodes
 */
function print_html( array $nodes ): void {
	Html_Renderer::print( $nodes );
}
