<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Conditional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Conditional\Comparison_Token;

final class Twig_Comparison extends Comparison_Token {
	public const COMPARISON_EMPTY = '|default(';
	public const COMPARISON_OR    = ' or ';

	public function get_token(): string {
		$token = parent::get_token();

		if ( self::COMPARISON_EMPTY === $this->operator_escaped ) {
			return sprintf( '%s)', $token );
		}

		return $token;
	}
}
