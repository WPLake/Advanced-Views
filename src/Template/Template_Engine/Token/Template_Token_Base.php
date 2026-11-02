<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Printer;

abstract class Template_Token_Base implements Template_Token {
	/**
	 * @var class-string<Token_Printer>
	 */
	protected string $printer_class;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class ) {
		$this->printer_class = $printer_class;
	}

	/**
	 * @return static
	 */
	public function clone(): self {
		return clone $this;
	}
}
