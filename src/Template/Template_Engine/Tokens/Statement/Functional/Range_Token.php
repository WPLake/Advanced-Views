<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Functional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Statement_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Statement_Token_Base;

abstract class Range_Token extends Statement_Token_Base {
	protected ?Statement_Token $from = null;
	protected ?Statement_Token $to   = null;

	public function set_from( Statement_Token $from ): self {
		$this->from = $from;

		return $this;
	}

	public function set_to( Statement_Token $to ): self {
		$this->to = $to;

		return $this;
	}

	public function get_statement(): string {
		return $this->get_token();
	}

	public function get_raw_statement(): string {
		return $this->get_token();
	}

	protected static function bare( ?Statement_Token $token ): string {
		return $token instanceof Statement_Token ?
			$token->get_token() :
			'';
	}
}
