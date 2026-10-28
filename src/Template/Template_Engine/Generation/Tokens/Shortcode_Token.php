<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;

/**
 * WordPress shortcode, e.g. [name attr="value"], with the variable (engine-specific) or plain values
 */
class Shortcode_Token extends Template_Token_Base {
	protected string $name;
	/**
	 * @var array<string,Variable_Token|string>
	 */
	protected array $attributes;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, string $name ) {
		parent::__construct( $printer_class );

		$this->name       = $name;
		$this->attributes = array();
	}

	/**
	 * @param array<string,Variable_Token|string> $attributes
	 */
	public function set_attributes( array $attributes ): self {
		$this->attributes = $attributes;

		return $this;
	}

	public function print_token(): void {
		printf( '[%s', esc_html( $this->name ) );

		foreach ( $this->attributes as $name => $value ) {
			printf( ' %s="', esc_html( $name ) );

			if ( $value instanceof Variable_Token ) {
				$value->display();
			} else {
				echo esc_html( $value );
			}

			echo '"';
		}

		echo ']';
	}

	public function display(): void {
		$this->print_token();
	}

	public function raw_display(): void {
		$this->print_token();
	}
}
