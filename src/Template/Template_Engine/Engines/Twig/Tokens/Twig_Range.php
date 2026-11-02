<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Range_Token;

final class Twig_Range extends Range_Token {
	public function get_token(): string {
		$parts = array(
			self::bare( $this->from ),
			self::bare( $this->to ),
		);

		return join( '..', $parts );
	}
}
