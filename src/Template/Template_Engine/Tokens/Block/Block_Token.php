<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Block;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;

/**
 * A token wrapping its nodes (get_nodes()) into the open and close parts, e.g. a loop.
 */
interface Block_Token extends Html_Element {
	public function get_open_html(): string;

	public function get_close_html(): string;
}
