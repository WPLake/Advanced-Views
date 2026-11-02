<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Variable\Assignment_Token;

final class Twig_Assignnment extends Assignment_Token {
	public function get_token(): string {
		$variable = self::bare( $this->variable );
		$value    = self::bare( $this->value );

		return sprintf( '{%% set %s = %s %%}', $variable, $value );
	}
}
