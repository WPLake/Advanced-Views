<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Assignment_Token;

final class PHP_Assignment extends Assignment_Token {
	public function print_token(): void {
		echo '<? ';

		if ( $this->variable instanceof Template_Token ) {
			$this->variable->print_token();
		}

		echo ' = ';

		if ( $this->value instanceof Template_Token ) {
			$this->value->print_token();
		}

		echo '; ?>';
	}
}
