<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

final class Text_Formatter {
	private string $line_break_symbol;
	private string $tab_symbol;

	protected Html_Escaper $escaper;
	protected int $tab_depth;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper   = $escaper;
		$this->tab_depth = 0;

		$this->line_break_symbol = "\r\n";
		$this->tab_symbol        = "\t";
	}

	public function set_line_break_symbol( string $line_break_symbol ): self {
		$this->line_break_symbol = $line_break_symbol;

		return $this;
	}

	public function set_tab_symbol( string $tab_symbol ): self {
		$this->tab_symbol = $tab_symbol;

		return $this;
	}

	public function new_line( int $count = 1 ): string {
		$line_breaks = str_repeat( $this->line_break_symbol, $count );

		return $this->escaper->esc_html( $line_breaks );
	}

	public function indent_line(): string {
		$new_line = $this->new_line();
		$tabs     = $this->tabulate();

		$line_parts = array( $new_line, $tabs );
		return join( '', $line_parts );
	}

	/**
	 * @param Closure():string $render_content
	 */
	public function tab_shift( Closure $render_content, int $step = 1 ): string {
		$this->tab_depth += $step;

		$tabs    = $this->tabulate();
		$content = $render_content();

		$this->tab_depth -= $step;

		$content_parts = array( $tabs, $content );
		return join( '', $content_parts );
	}

	public function tabulate(): string {
		$tabs = str_repeat( $this->tab_symbol, $this->tab_depth );

		return $this->escaper->esc_html( $tabs );
	}
}
