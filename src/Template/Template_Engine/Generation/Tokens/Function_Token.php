<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Statement\Statement_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;

class Function_Token extends Statement_Token_Base {
	protected string $name;
	/**
	 * @var Statement_Token[]
	 */
	protected array $arguments = array();

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, string $name ) {
		parent::__construct( $printer_class );

		$this->name = $name;
	}

	/**
	 * @param Statement_Token[] $arguments
	 */
	public function set_arguments( array $arguments ): self {
		$this->arguments = $arguments;

		return $this;
	}

	public function get_token(): string {
		$arguments = array_map(
			fn( Statement_Token $argument ): string => $argument->get_token(),
			$this->arguments
		);

		$joined_arguments = join( ', ', $arguments );

		return sprintf( '%s(%s)', esc_html( $this->name ), $joined_arguments );
	}
}
