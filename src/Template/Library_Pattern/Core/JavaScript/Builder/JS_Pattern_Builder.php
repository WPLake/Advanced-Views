<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Pattern\JS_Pattern;

/**
 * The pattern's code is rendered one level deeper than the field lookup wrapping it.
 * It requires the same Text_Formatter instance in all the builders, as the depth is its state.
 */
final class JS_Pattern_Builder {
	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;
	protected Setup_Builder $setup_builder;
	protected string $multiple_item_variable;

	public function __construct(
		Html_Escaper $escaper,
		Text_Formatter $formatter,
		Setup_Builder $setup_builder
	) {
		$this->escaper       = $escaper;
		$this->formatter     = $formatter;
		$this->setup_builder = $setup_builder;

		$this->multiple_item_variable = 'item';
	}

	public function set_multiple_item_variable( string $multiple_item_variable ): self {
		$this->multiple_item_variable = $multiple_item_variable;

		return $this;
	}

	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	public function build_single_item( string $js_pattern, string $name, string $selector ): string {
		$selector_safe = $this->escaper->esc_html( $selector );
		$name_safe     = $this->escaper->esc_html( $name );
		$new_line      = $this->formatter->new_line();
		$opening_lines = array(
			sprintf(
				"var %s = this.querySelector('%s');",
				$name_safe,
				$selector_safe
			),
			sprintf( 'if (%s) {', $name_safe ),
		);
		$opening       = join( $new_line, $opening_lines );

		return $this->wrap(
			$js_pattern,
			$opening,
			'}',
			$name
		);
	}

	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	public function build_multiple_items( string $js_pattern, string $selector ): string {
		$selector_safe = $this->escaper->esc_html( $selector );
		$opening       = sprintf( "this.querySelectorAll('%s').forEach(item => {", $selector_safe );

		return $this->wrap(
			$js_pattern,
			$opening,
			'});',
			$this->multiple_item_variable
		);
	}

	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	protected function wrap( string $js_pattern, string $opening, string $closing, string $outer_wrapper ): string {
		$content  = $this->formatter->tab_shift(
			fn() => $this->setup_builder->build_setup( $js_pattern, $outer_wrapper )
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
