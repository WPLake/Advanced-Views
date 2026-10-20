<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;

class Function_Token extends Template_Token_Base {
	protected string $name;
	/**
	 * @var Template_Token[]
	 */
	protected array $arguments;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, string $name ) {
		parent::__construct( $printer_class );

		$this->name = $name;
	}

	public function print_token(): void {
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

	public function display(): void {
		$this->printer_class::display( $this );
	}

	public function raw_display(): void {
		$this->printer_class::raw_display( $this );
	}

	protected function print_arguments(): void {
		$is_first = true;

		foreach ( $this->arguments as $argument ) {
			if ( $is_first ) {
				$is_first = false;
			} else {
				echo ', ';
			}

			$argument->print_token();
		}
	}
}
