<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token;

class Comparison_Token extends Statement_Token_Base {
	const COMPARISON_GREATER = ' > ';
	const COMPARISON_EQUAL   = ' == ';
	const COMPARISON_EMPTY   = ' ?: ';
	const COMPARISON_OR      = ' || ';

	protected ?Statement_Token $left    = null;
	protected ?Statement_Token $right   = null;
	protected string $operator_escaped = '';

	public function set_left_operand( Statement_Token $left ): self {
		$this->left = $left;

		return $this;
	}

	public function set_right_operand( Statement_Token $right ): self {
		$this->right = $right;

		return $this;
	}

	public function set_comparison_greater(): self {
		$this->operator_escaped = static::COMPARISON_GREATER;

		return $this;
	}

	public function set_comparison_equal(): self {
		$this->operator_escaped = static::COMPARISON_EQUAL;

		return $this;
	}

	public function set_comparison_empty(): self {
		$this->operator_escaped = static::COMPARISON_EMPTY;

		return $this;
	}

	public function set_comparison_or(): self {
		$this->operator_escaped = static::COMPARISON_OR;

		return $this;
	}

	public function get_token(): string {
		$left  = $this->left instanceof Statement_Token ?
			$this->left->get_token() :
			'';
		$right = $this->right instanceof Statement_Token ?
			$this->right->get_token() :
			'';

		return $left . $this->operator_escaped . $right;
	}
}
