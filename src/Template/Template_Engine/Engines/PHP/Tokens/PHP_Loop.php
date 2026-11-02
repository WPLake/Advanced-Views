<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Loop_Token;

final class PHP_Loop extends Loop_Token {
	public function get_open_html(): string {
		$source = self::bare_token( $this->source_var );
		$index  = $this->index_var->get_token();
		$item   = self::bare_token( $this->item_var );

		return sprintf( '<? foreach (%s as %s => %s): ?>', $source, $index, $item );
	}

	public function get_close_html(): string {
		return '<? endforeach; ?>';
	}
}
