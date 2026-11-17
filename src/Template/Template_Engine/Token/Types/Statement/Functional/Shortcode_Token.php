<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

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
	public function __construct( string $printer_class, Html_Escaper $escaper, string $name ) {
		parent::__construct( $printer_class, $escaper );

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
		$escaped_name = $this->escaper->esc_html( $this->name );
		$token        = sprintf( '[%s', $escaped_name );

		foreach ( $this->attributes as $name => $value ) {
			$value_html = $value instanceof Variable_Token ?
				$value->get_statement() :
				$this->escaper->esc_html( $value );

			$escaped_attr_name = $this->escaper->esc_html( $name );

			$token .= sprintf( ' %s="%s"', $escaped_attr_name, $value_html );
		}

		return sprintf( '%s]', $token );
	}

	public function get_statement(): string {
		return $this->get_token();
	}
}
