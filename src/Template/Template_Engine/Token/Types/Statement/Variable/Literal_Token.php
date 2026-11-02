<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Token_Printer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-type Literal_Value string|numeric|bool|Statement_Token|mixed[]
 */
abstract class Literal_Token extends Statement_Token_Base {
	/**
	 * @var Literal_Value
	 */
	public $value;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 * @param Literal_Value $value
	 */
	public function __construct( string $printer_class, $value ) {
		parent::__construct( $printer_class );

		$this->value = $value;
	}

	public function get_token(): string {
		return $this->literally( $this->value );
	}

	/**
	 * @param mixed $value
	 */
	protected function literally( $value ): string {
		if ( is_string( $value ) ) {
			return sprintf( "'%s'", esc_html( $value ) );
		}

		return $this->as_string( $value );
	}

	/**
	 * @param mixed $value
	 */
	protected function as_string( $value ): string {
		if ( is_bool( $value ) ) {
			return $value ?
				'true' :
				'false';
		}

		if ( is_array( $value ) ) {
			return $this->array_literal( $value );
		}

		if ( $value instanceof Statement_Token ) {
			return $value->get_token();
		}

		$text = string( $value );

		return esc_html( $text );
	}

	/**
	 * @param mixed[] $value
	 */
	abstract protected function array_literal( array $value ): string;
}
