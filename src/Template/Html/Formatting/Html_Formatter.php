<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

final class Html_Formatter {
	protected Html_Escaper $escaper;
	protected string $break_symbol;
	protected string $tab_symbol;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper      = $escaper;
		$this->break_symbol = "\r\n";
		$this->tab_symbol   = "\t";
	}

	public function set_break_symbol( string $break_symbol ): self {
		$this->break_symbol = $break_symbol;

		return $this;
	}

	public function set_tab_symbol( string $tab_symbol ): self {
		$this->tab_symbol = $tab_symbol;

		return $this;
	}

	public function new_line( int $count = 1 ): self {
		$symbols = str_repeat( $this->break_symbol, $count );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->escaper->esc_html( $symbols );

		return $this;
	}

	public function tab( int $count = 1 ): self {
		$symbols = str_repeat( $this->tab_symbol, $count );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->escaper->esc_html( $symbols );

		return $this;
	}

	public function line_start( int $tabs_count ): self {
		return $this->new_line()
					->tab( $tabs_count );
	}
}
