<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Echo_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-type Literal_Value string|numeric|bool|Template_Token|mixed[]
 */
abstract class Literal_Token extends Template_Token_Base {
	/**
	 * @var Literal_Value
	 */
	public $value;

	/**
	 * @param class-string<Echo_Token> $echo_token_class
	 * @param Literal_Value $value
	 */
	public function __construct( string $echo_token_class, $value ) {
		parent::__construct( $echo_token_class );

		$this->value = $value;
	}

	public function print_token(): void {
		$this->print_literally( $this->value );
	}

	/**
	 * @param mixed $value
	 */
	protected function print_literally( $value ): void {
		if ( is_string( $value ) ) {
			printf( "'%s'", esc_html( $value ) );
		} else {
			$this->print_as_string( $value );
		}
	}

	/**
	 * @param mixed $value
	 */
	protected function print_as_string( $value ): void {
		if ( is_bool( $value ) ) {
			echo $value ?
				'true' :
				'false';
		} elseif ( is_array( $value ) ) {
			$this->print_array( $value );
		} elseif ( $value instanceof Literal_Token ) {
			// e.g. Variable_Token.
			$value->print_token();
		} else {
			echo esc_html(
				string( $value )
			);
		}
	}

	/**
	 * @param mixed[] $value
	 */
	abstract protected function print_array( array $value ): void;
}
