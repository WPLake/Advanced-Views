<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;

abstract class Range_Token extends Template_Token_Base {
	protected ?Template_Token $from = null;
	protected ?Template_Token $to   = null;

	public function set_from( Template_Token $from ): self {
		$this->from = $from;

		return $this;
	}

	public function set_to( Template_Token $to ): self {
		$this->to = $to;

		return $this;
	}
}
