<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Block;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Block\Tokens\Loop_Token;

final class Blade_Loop extends Loop_Token {
	public function get_open_statement(): string {
		$source = self::bare_token( $this->source_var );
		$index  = $this->index_var->get_token();
		$item   = self::bare_token( $this->item_var );

		return sprintf( '@foreach (%s as %s => %s)', $source, $index, $item );
	}

	public function get_close_statement(): string {
		return '@endforeach';
	}
}
