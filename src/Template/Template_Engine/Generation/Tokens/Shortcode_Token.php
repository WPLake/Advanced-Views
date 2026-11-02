<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;

/**
 * WordPress shortcode, e.g. [name attr="value"], with the variable (engine-specific) or plain values
 */
class Shortcode_Token extends Statement_Token_Base {
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

	public function get_token(): string {
		$token = sprintf( '[%s', esc_html( $this->name ) );

		foreach ( $this->attributes as $name => $value ) {
			$value_html = $value instanceof Variable_Token ?
				$value->get_statement() :
				esc_html( $value );

			$token .= sprintf( ' %s="%s"', esc_html( $name ), $value_html );
		}

		return sprintf( '%s]', $token );
	}

	public function get_statement(): string {
		return $this->get_token();
	}

	public function get_raw_statement(): string {
		return $this->get_token();
	}
}
