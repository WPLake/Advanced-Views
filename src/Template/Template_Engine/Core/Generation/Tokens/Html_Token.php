<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token\Template_Token_Base;

class Html_Token extends Template_Token_Base {
	/**
	 * @var callable
	 */
	protected $printer;

	public function __construct( callable $printer ) {
		$this->printer = $printer;
	}

	public function print(): void {
		( $this->printer )();
	}
}
