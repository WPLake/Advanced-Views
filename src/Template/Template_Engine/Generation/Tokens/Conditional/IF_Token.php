<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;

abstract class IF_Token extends Template_Token_Base {
	protected ?IF_Branch $if_branch = null;
	/**
	 * @var IF_Branch[]
	 */
	protected array $elseif_branches  = array();
	protected ?IF_Branch $else_branch = null;

	public function set_if_branch( IF_Branch $branch ): self {
		$this->if_branch = $branch;

		return $this;
	}

	public function set_elseif_branch( IF_Branch $branch ): self {
		$this->elseif_branches[] = $branch;

		return $this;
	}

	public function set_else_branch( IF_Branch $branch ): self {
		$this->else_branch = $branch;

		return $this;
	}
}
