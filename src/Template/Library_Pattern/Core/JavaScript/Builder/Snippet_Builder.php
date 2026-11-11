<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class Snippet_Builder {
	const MULTIPLE_ITEM_VARIABLE = 'item';

	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->escaper   = $escaper;
		$this->formatter = $formatter;
	}

	/**
	 * The content is rendered one level deeper, with the wrapper's variable name given to the callback.
	 * It requires the same Text_Formatter instance in the content builders, as the depth is its state.
	 *
	 * @param Closure(string):string $render_content
	 */
	public function build_single( string $name, string $selector, Closure $render_content ): string {
		$selector_safe = $this->escaper->esc_html( $selector );
		$name_safe     = $this->escaper->esc_html( $name );
		$new_line      = $this->formatter->new_line();
		$opening_lines = array(
			sprintf( "var %s = this.querySelector('%s');", $name_safe, $selector_safe ),
			sprintf( 'if (%s) {', $name_safe ),
		);
		$opening       = join( $new_line, $opening_lines );

		return $this->wrap( $opening, '}', $name, $render_content );
	}

	/**
	 * @param Closure(string):string $render_content
	 */
	public function build_multiple( string $selector, Closure $render_content ): string {
		$selector_safe = $this->escaper->esc_html( $selector );
		$opening       = sprintf( "this.querySelectorAll('%s').forEach(item => {", $selector_safe );

		return $this->wrap( $opening, '});', self::MULTIPLE_ITEM_VARIABLE, $render_content );
	}

	/**
	 * @param Closure(string):string $render_content
	 */
	protected function wrap( string $opening, string $closing, string $outer_wrapper, Closure $render_content ): string {
		$content  = $this->formatter->tab_shift(
			fn() => $render_content( $outer_wrapper )
		);
		$new_line = $this->formatter->new_line();
		$lines    = array(
			$opening,
			$content,
			$closing,
		);

		return join( $new_line, $lines );
	}
}
