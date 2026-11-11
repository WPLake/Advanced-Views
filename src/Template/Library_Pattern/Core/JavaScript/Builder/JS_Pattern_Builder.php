<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder\Object\Object_Builder;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Pattern\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Pattern\JS_Variables;

final class JS_Pattern_Builder {
	protected Object_Builder $object_builder;
	protected Snippet_Builder $snippet_builder;
	protected Text_Formatter $formatter;

	public function __construct( Object_Builder $object_builder, Snippet_Builder $snippet_builder, Text_Formatter $formatter ) {
		$this->object_builder = $object_builder;
		$this->snippet_builder  = $snippet_builder;
		$this->formatter      = $formatter;
	}

	/**
	 * The code with the field lookup.
	 *
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	public function build_snippet(
		string $js_pattern,
		string $name,
		string $selector,
		bool $is_multiple
	): string {
		$render_content = fn( string $outer_wrapper ) => $this->build( $js_pattern, $outer_wrapper );

		return $this->snippet_builder->build(
			$name,
			$selector,
			$is_multiple,
			$render_content
		);
	}

	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	public function build( string $js_pattern, string $outer_wrapper ): string {
		$variables                = new JS_Variables();
		$variables->outer_wrapper = $outer_wrapper;

		$options_code = $this->compose_options( $js_pattern, $variables );
		$setup_code   = $this->compose_setup( $js_pattern, $variables );

		$blocks = array(
			$options_code,
			$setup_code,
		);

		return $this->join_blocks( $blocks );
	}

	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	protected function compose_options( string $js_pattern, JS_Variables $variables ): string {
		$options   = $js_pattern::get_js_options();
		$is_filled = count( $options ) > 0;

		if ( $is_filled ) {
			return $this->object_builder->build( $options, $variables->options );
		}

		return '';
	}

	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	protected function compose_setup( string $js_pattern, JS_Variables $variables ): string {
		$setup      = $js_pattern::get_js_setup( $variables );
		$tabs       = $this->formatter->tabulate();
		$statements = array();

		foreach ( $setup as $line ) {
			$statements[] = sprintf( '%s%s;', $tabs, $line );
		}

		$new_line = $this->formatter->new_line();
		return join( $new_line, $statements );
	}

	/**
	 * The blocks are separated by an empty line.
	 *
	 * @param string[] $blocks
	 */
	protected function join_blocks( array $blocks ): string {
		$filled_blocks = array_filter(
			$blocks,
			fn( string $block ) => strlen( $block ) > 0
		);

		$new_lines = $this->formatter->new_line( 2 );
		return join( $new_lines, $filled_blocks );
	}
}
