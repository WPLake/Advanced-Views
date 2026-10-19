<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token;

defined( 'ABSPATH' ) || exit;

abstract class Template_Token_Base implements Template_Token {
	public function __toString(): string {
		ob_start();

		$this->print();

		return (string) ob_get_clean();
	}
}
