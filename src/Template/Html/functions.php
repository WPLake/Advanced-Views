<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Composed_Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

function tag( string $name ): Html_Tag {
	return new Html_Tag( $name );
}

/**
 * @param array<string|Htmlable> $parts
 */
function compose_html( array $parts ): Htmlable {
	return new Composed_Htmlable( $parts );
}
