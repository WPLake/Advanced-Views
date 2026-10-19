<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Tokens\Variable;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token\Template_Token;

defined( 'ABSPATH' ) || exit;

abstract class Assignment_Token extends Template_Token_Base {
	protected ?Variable_Token $variable = null;
	protected ?Template_Token $value    = null;

	public function set_variable( Variable_Token $variable ): self {
		$this->variable = $variable;

		return $this;
	}

	public function set_value( Template_Token $value ): self {
		$this->value = $value;

		return $this;
	}
}
