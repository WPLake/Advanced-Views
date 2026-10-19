<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token\Template_Token_Base;

abstract class Comment_Token extends Template_Token_Base {
	protected string $content;

	public function __construct( string $content ) {
		$this->content = $content;
	}
}
