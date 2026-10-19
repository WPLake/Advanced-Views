<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;

abstract class Echo_Token extends Template_Token_Base {
	protected Template_Token $content;
	protected bool $is_raw;

	public function __construct( Template_Token $content ) {
		parent::__construct( static::class );

		$this->content = $content;

		$this->is_raw = false;
	}

	public function set_is_raw( bool $is_raw ): self {
		$this->is_raw = $is_raw;

		return $this;
	}
}
