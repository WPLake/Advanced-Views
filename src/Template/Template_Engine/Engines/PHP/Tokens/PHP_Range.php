<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Range_Token;

final class PHP_Range extends Range_Token {
	public function get_token(): string {
		$from = self::bare( $this->from );
		$to   = self::bare( $this->to );

		return sprintf( 'range(%s, %s)', $from, $to );
	}
}
