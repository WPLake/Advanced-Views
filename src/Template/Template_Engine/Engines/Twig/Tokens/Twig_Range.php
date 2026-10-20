<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Range_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;

final class Twig_Range extends Range_Token {
	public function print_token(): void {
		if ( $this->from instanceof Template_Token ) {
			$this->from->print_token();
		}

		echo '..';

		if ( $this->to instanceof Template_Token ) {
			$this->to->print_token();
		}
	}

	public function display(): void {
		$this->print_token();
	}

	public function raw_display(): void {
		$this->print_token();
	}
}
