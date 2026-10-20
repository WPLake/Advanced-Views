<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Comment_Token;

final class PHP_Comment extends Comment_Token {
	public function print_token(): void {
		printf(
			'/* %s */',
			esc_html( $this->content )
		);
	}

	public function display(): void {
		$this->print_token();
	}

	public function raw_display(): void {
		$this->print_token();
	}
}
