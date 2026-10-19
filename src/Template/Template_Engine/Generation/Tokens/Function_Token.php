<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;

class Function_Token extends Template_Token_Base {
	protected string $name;
	/**
	 * @var Template_Token[]
	 */
	protected array $arguments;

	/**
	 * @param class-string<Echo_Token> $echo_token_class
	 */
	public function __construct( string $echo_token_class, string $name ) {
		parent::__construct( $echo_token_class );

		$this->name = $name;
	}

	public function print(): void {
		printf( '%s(', esc_html( $this->name ) );
		$this->print_arguments();
		echo ')';
	}

	/**
	 * @param Template_Token[] $arguments
	 */
	public function set_arguments( array $arguments ): self {
		$this->arguments = $arguments;

		return $this;
	}

	protected function print_arguments(): void {
		$is_first = true;

		foreach ( $this->arguments as $argument ) {
			if ( $is_first ) {
				$is_first = false;
			} else {
				echo ', ';
			}

			$argument->print();
		}
	}
}
