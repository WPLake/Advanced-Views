<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;

abstract class Comment_Token extends Template_Token_Base {
	protected string $content;

	/**
	 * @param class-string<Echo_Token> $echo_token_class
	 */
	public function __construct( string $echo_token_class, string $content ) {
		parent::__construct( $echo_token_class );

		$this->content = $content;
	}
}
