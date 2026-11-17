<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Conditional\Comparison_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Conditional\Conditional_Value_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Function_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Shortcode_Token;

abstract class Token_Factory_Base implements Token_Factory {
	/**
	 * @var class-string<Token_Printer>
	 */
	protected string $printer_class;
	protected Html_Escaper $escaper;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, Html_Escaper $escaper ) {
		$this->printer_class = $printer_class;
		$this->escaper       = $escaper;
	}

	public function comparison(): Comparison_Token {
		return new Comparison_Token( $this->printer_class, $this->escaper );
	}

	public function if_branch(): IF_Branch {
		return new IF_Branch();
	}

	public function function( string $name ): Function_Token {
		return new Function_Token(
			$this->printer_class,
			$this->escaper,
			$name
		);
	}

	public function conditional_value(): Conditional_Value_Token {
		return new Conditional_Value_Token( $this->printer_class, $this->escaper );
	}

	public function shortcode( string $name ): Shortcode_Token {
		return new Shortcode_Token(
			$this->printer_class,
			$this->escaper,
			$name
		);
	}
}
