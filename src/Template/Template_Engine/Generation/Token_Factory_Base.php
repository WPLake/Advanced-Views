<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\Comparison_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\Conditional_Value_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Function_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Html_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Shortcode_Token;

abstract class Token_Factory_Base implements Token_Factory {
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

	public function html( callable $printer ): Html_Token {
		return new Html_Token( $this->printer_class, $printer );
	}

	public function comparison(): Comparison_Token {
		return new Comparison_Token( $this->printer_class );
	}

	public function if_branch(): IF_Branch {
		return new IF_Branch();
	}

	public function function( string $name ): Function_Token {
		return new Function_Token( $this->printer_class, $name );
	}

	public function conditional_value(): Conditional_Value_Token {
		return new Conditional_Value_Token( $this->printer_class );
	}

	public function shortcode( string $name ): Shortcode_Token {
		return new Shortcode_Token( $this->printer_class, $name );
	}
}
