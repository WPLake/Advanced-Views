<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token;

class Conditional_Value_Token extends Statement_Token_Base {
	protected ?Comparison_Token $comparison  = null;
	protected ?Statement_Token $accept_value  = null;
	protected ?Statement_Token $decline_value = null;

	public function set_comparison( Comparison_Token $comparison ): self {
		$this->comparison = $comparison;

		return $this;
	}

	public function set_accept_value( Statement_Token $accept_value ): self {
		$this->accept_value = $accept_value;

		return $this;
	}

	public function set_decline_value( Statement_Token $decline_value ): self {
		$this->decline_value = $decline_value;

		return $this;
	}

	public function get_token(): string {
		$comparison = self::bare( $this->comparison );
		$accept     = self::bare( $this->accept_value );
		$decline    = self::bare( $this->decline_value );

		return sprintf( '%s ? %s : %s', $comparison, $accept, $decline );
	}

	protected static function bare( ?Statement_Token $token ): string {
		return $token instanceof Statement_Token ?
			$token->get_token() :
			'';
	}
}
