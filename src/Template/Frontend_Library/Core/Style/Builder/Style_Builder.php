<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

/**
 * The only place that turns CSS rules ([property => value] OR [innerSelector => [property => value]]) into the code.
 * Only the given field selector is escaped, as the rules are the developer's constants.
 * Without a field selector (e.g. the provider's inline styles) the rules must be selector-keyed only.
 */
final class Style_Builder {
	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->escaper   = $escaper;
		$this->formatter = $formatter;
	}

	/**
	 * @param array<string, string|array<string, string>> $rules
	 */
	public function build( array $rules, string $field_selector = '' ): string {
		$selector_safe = $this->escaper->esc_html( $field_selector );
		$blocks        = array();

		$own_declarations = array_filter( $rules, 'is_string' );
		$has_selector     = strlen( $field_selector ) > 0;
		$has_own          = array() !== $own_declarations;

		if ( $has_selector && $has_own ) {
			$blocks[] = $this->compose_block( $selector_safe, $own_declarations );
		}

		foreach ( $rules as $inner_selector => $declarations ) {
			if ( is_array( $declarations ) ) {
				$full_selector = $this->join_selector( $selector_safe, $inner_selector );
				$blocks[]      = $this->compose_block( $full_selector, $declarations );
			}
		}

		$block_separator = $this->formatter->new_line( 2 );

		return join( $block_separator, $blocks );
	}

	protected function join_selector( string $field_selector, string $inner_selector ): string {
		$has_selector = strlen( $field_selector ) > 0;

		if ( $has_selector ) {
			$separator = 0 === strpos( $inner_selector, ':' ) ?
				'' :
				' ';

			return $field_selector . $separator . $inner_selector;
		}

		return $inner_selector;
	}

	/**
	 * @param array<string,string> $declarations
	 */
	protected function compose_block( string $selector, array $declarations ): string {
		$new_line     = $this->formatter->new_line();
		$lines        = $this->formatter->tab_shift(
			fn() => $this->compose_declarations( $declarations )
		);
		$opening_line = sprintf( '%s {', $selector );
		$block_lines  = array( $opening_line, $lines, '}' );

		return join( $new_line, $block_lines );
	}

	/**
	 * @param array<string,string> $declarations
	 */
	protected function compose_declarations( array $declarations ): string {
		$new_line = $this->formatter->new_line();
		$tabs     = $this->formatter->tabulate();
		$lines    = array();

		foreach ( $declarations as $property => $value ) {
			$lines[] = sprintf(
				'%s%s: %s;',
				$tabs,
				$property,
				$value
			);
		}

		return join( $new_line, $lines );
	}
}
