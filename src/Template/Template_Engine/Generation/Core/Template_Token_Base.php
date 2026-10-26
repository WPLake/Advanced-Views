<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core;

use Org\Wplake\Advanced_Views\Template\Html\Html_Escaper;

defined( 'ABSPATH' ) || exit;

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

	public function to_html( Html_Escaper $escaper ): string {
		ob_start();
		$this->display();

		return (string) ob_get_clean();
	}

	/**
	 * @return static
	 */
	public function clone(): self {
		return clone $this;
	}
}
