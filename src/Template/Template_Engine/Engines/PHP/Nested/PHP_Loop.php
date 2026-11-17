<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Nested;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;

final class PHP_Loop extends Loop_Token {
	protected function get_open_statement(): string {
		$source = self::bare_token( $this->source_var );
		$index  = $this->index_var->get_token();
		$item   = self::bare_token( $this->item_var );

		return sprintf( '<? foreach (%s as %s => %s): ?>', $source, $index, $item );
	}

	protected function get_end_statement(): string {
		return '<? endforeach; ?>';
	}
}
