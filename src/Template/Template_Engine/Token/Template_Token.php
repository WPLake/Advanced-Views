<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;

/**
 * A template token, which is rendered as an HTML node: a statement, a block or a nested one.
 */
interface Template_Token extends Html_Element {
}
