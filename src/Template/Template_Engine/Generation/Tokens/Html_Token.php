<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;

class Html_Token extends Template_Token_Base {
	/**
	 * @var callable
	 */
	protected $printer;

	/**
	 * @param class-string<Echo_Token> $echo_token_class
	 */
	public function __construct( string $echo_token_class, callable $printer ) {
		parent::__construct( $echo_token_class );

		$this->printer = $printer;
	}

	public function print(): void {
		( $this->printer )();
	}
}
