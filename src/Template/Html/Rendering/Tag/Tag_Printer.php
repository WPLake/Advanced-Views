<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

/**
 * Prints the tag parts, the content and the self-closing detection are up to Html_Renderer.
 */
interface Tag_Printer {
	public function print_opening( Html_Tag $tag ): void;

	public function print_closing( Html_Tag $tag ): void;

	/**
	 * A tag without a closing part, e.g. `br`
	 */
	public function print_void( Html_Tag $tag ): void;
}
