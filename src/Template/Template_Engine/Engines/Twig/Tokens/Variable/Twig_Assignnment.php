<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Assignment_Token;

final class Twig_Assignnment extends Assignment_Token {
	public function print_token(): void {
		echo '{% set ';

		if ( $this->variable instanceof Template_Token ) {
			$this->variable->print_token();
		}

		echo ' = ';

		if ( $this->value instanceof Template_Token ) {
			$this->value->print_token();
		}

		echo ' %}';
	}

	public function display(): void {
		$this->print_token();
	}

	public function raw_display(): void {
		$this->print_token();
	}
}
