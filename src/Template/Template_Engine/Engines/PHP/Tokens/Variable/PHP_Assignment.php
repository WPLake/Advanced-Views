<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Assignment_Token;

final class PHP_Assignment extends Assignment_Token {
	public function get_token(): string {
		$variable = self::bare( $this->variable );
		$value    = self::bare( $this->value );

		return sprintf( '<? %s = %s; ?>', $variable, $value );
	}
}
