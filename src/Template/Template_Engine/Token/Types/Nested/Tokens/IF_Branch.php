<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;

/**
 * @phpstan-import-type Html_Node from Html_Element
 */
final class IF_Branch {
	public ?Statement_Token $condition = null;
	/**
	 * @var Html_Node[]
	 */
	public array $body = array();

	public function set_condition( Statement_Token $condition ): self {
		$this->condition = $condition;

		return $this;
	}

	/**
	 * @param Html_Node[] $body
	 */
	public function set_body( array $body ): self {
		$this->body = $body;

		return $this;
	}
}
