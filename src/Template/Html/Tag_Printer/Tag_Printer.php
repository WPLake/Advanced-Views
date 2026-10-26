<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Tag_Printer;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

interface Tag_Printer {
	/**
	 * @param Closure $print_content prints the tag content
	 */
	public function print_pair_tag( Html_Tag $tag, Closure $print_content ): void;

	/**
	 * A self-closing tag, e.g. `br`
	 */
	public function print_single_tag( Html_Tag $tag ): void;

	/**
	 * A non-tag node of the tag list: text, an Htmlable or a callback
	 *
	 * @param string|Htmlable|Closure $node
	 */
	public function print_node( $node ): void;
}
