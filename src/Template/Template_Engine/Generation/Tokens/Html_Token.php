<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;

class Html_Token extends Template_Token_Base {
	/**
	 * @var callable
	 */
	protected $printer;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, callable $printer ) {
		parent::__construct( $printer_class );

		$this->printer = $printer;
	}

	public function print_token(): void {
		( $this->printer )();
	}
}
