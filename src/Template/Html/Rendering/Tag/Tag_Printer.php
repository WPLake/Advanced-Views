<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

interface Tag_Printer {
	/**
	 * @param callable():mixed|null $print_content prints the tag content
	 */
	public function print_pair_tag( Html_Tag $tag, ?callable $print_content = null ): void;

	/**
	 * A self-closing tag, e.g. `br`
	 */
	public function print_single_tag( Html_Tag $tag ): void;
}
