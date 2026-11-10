<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class JS_Snippet_Builder {
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
	public function build(
		string $auto_discover_name,
		string $name,
		string $selector,
		bool $is_multiple,
		Closure $render_content
	): string {
		$outer_wrapper = $is_multiple ?
			self::MULTIPLE_ITEM_VARIABLE :
			$name;

		$opening = $this->compose_opening( $name, $selector, $is_multiple );
		$content = $this->formatter->tab_shift(
			fn() => $render_content( $outer_wrapper )
		);
		$closing = $is_multiple ?
			'});' :
			'}';

		$lines = array(
			$opening,
			$content,
			$closing,
		);
		$new_line = $this->formatter->new_line();
		$code     = join( $new_line, $lines );

		return $this->wrap_with_markers( $auto_discover_name, $name, $code );
	}

	protected function compose_opening( string $name, string $selector, bool $is_multiple ): string {
		$selector_safe = $this->escaper->esc_html( $selector );

		if ( $is_multiple ) {
			return sprintf( "this.querySelectorAll('%s').forEach(item => {", $selector_safe );
		}

		$name_safe = $this->escaper->esc_html( $name );
		$new_line  = $this->formatter->new_line();
		$lines     = array(
			sprintf( "var %s = this.querySelector('%s');", $name_safe, $selector_safe ),
			sprintf( 'if (%s) {', $name_safe ),
		);

		return join( $new_line, $lines );
	}

	protected function wrap_with_markers( string $auto_discover_name, string $name, string $code ): string {
		$discover_safe = $this->escaper->esc_html( $auto_discover_name );
		$name_safe     = $this->escaper->esc_html( $name );
		$new_lines     = $this->formatter->new_line( 2 );

		$parts = array(
			$new_lines,
			sprintf( '/* %s : %s (auto-discover-begin) */', $discover_safe, $name_safe ),
			$new_lines,
			$code,
			$new_lines,
			sprintf( '/* %s : %s (auto-discover-end) */', $discover_safe, $name_safe ),
			$new_lines,
		);

		return join( '', $parts );
	}
}
