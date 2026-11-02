<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Block;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Template_Token;

/**
 * A token wrapping its nodes (get_nodes()) into the open and close parts, e.g. a loop.
 */
interface Block_Token extends Template_Token {
	public function get_open_statement(): string;

	public function get_close_statement(): string;
}
