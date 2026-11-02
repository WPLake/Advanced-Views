<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token;

abstract class Assignment_Token extends Statement_Token_Base {
	protected ?Variable_Token $variable = null;
	protected ?Statement_Token $value   = null;

	public function set_variable( Variable_Token $variable ): self {
		$this->variable = $variable;

		return $this;
	}

	public function set_value( Statement_Token $value ): self {
		$this->value = $value;

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
