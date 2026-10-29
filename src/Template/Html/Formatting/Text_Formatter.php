<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

final class Text_Formatter {
	public string $line_break_symbol;
	public string $tab_symbol;

	protected Html_Escaper $escaper;
	protected int $tab_depth;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper   = $escaper;
		$this->tab_depth = 0;

		$this->line_break_symbol = "\r\n";
		$this->tab_symbol        = "\t";
	}

	public function new_line( int $count = 1 ): self {
		$line_breaks = str_repeat( $this->line_break_symbol, $count );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->escaper->esc_html( $line_breaks );

		return $this;
	}

	public function indent_line(): self {
		return $this->new_line()
			->tabulate();
	}

	/**
	 * Callback return is ignored; mixed is declared for "fn() => print()" closures.
	 *
	 * @param callable():mixed $callback
	 */
	public function tab_shift( callable $callback, int $step = 1 ): self {
		$this->tab_depth += $step;

		$this->tabulate();
		$callback();

		$this->tab_depth -= $step;

		return $this;
	}

	public function tabulate(): self {
		$tabs = str_repeat( $this->tab_symbol, $this->tab_depth );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->escaper->esc_html( $tabs );

		return $this;
	}
}
