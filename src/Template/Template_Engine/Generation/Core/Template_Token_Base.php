<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Echo_Token;

abstract class Template_Token_Base implements Template_Token {
	/**
	 * @var class-string<Echo_Token>
	 */
	protected string $echo_token_class;

	/**
	 * @param class-string<Echo_Token> $echo_token_class
	 */
	public function __construct( string $echo_token_class ) {
		$this->echo_token_class = $echo_token_class;
	}

	public function to_display(): Echo_Token {
		return new $this->echo_token_class( $this );
	}

	public function __toString(): string {
		ob_start();

		$this->print();

		return (string) ob_get_clean();
	}
}
