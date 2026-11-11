<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Pattern\CSS_Pattern;

/**
 * The only place that turns the pattern's rules into the CSS code.
 * Only the given field selector is escaped, as the rules are the developer's constants.
 */
final class CSS_Pattern_Builder {
	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->escaper   = $escaper;
		$this->formatter = $formatter;
	}

	/**
	 * @param class-string<CSS_Pattern> $css_pattern
	 */
	public function build( string $css_pattern, string $field_selector ): string {
		$selector_safe = $this->escaper->esc_html( $field_selector );
		$rules         = $css_pattern::get_css_rules();
		$blocks        = array();

		$own_declarations = array_filter( $rules, 'is_string' );

		if ( array() !== $own_declarations ) {
			$blocks[] = $this->compose_block( $selector_safe, $own_declarations );
		}

		foreach ( $rules as $inner_selector => $declarations ) {
			if ( is_array( $declarations ) ) {
				$full_selector = $this->join_selector( $selector_safe, $inner_selector );
				$blocks[]      = $this->compose_block( $full_selector, $declarations );
			}
		}

		return join( $this->formatter->new_line( 2 ), $blocks );
	}

	protected function join_selector( string $field_selector, string $inner_selector ): string {
		$separator = 0 === strpos( $inner_selector, ':' ) ?
			'' :
			' ';

		return $field_selector . $separator . $inner_selector;
	}

	/**
	 * @param array<string,string> $declarations
	 */
	protected function compose_block( string $selector, array $declarations ): string {
		$new_line = $this->formatter->new_line();
		$lines    = $this->formatter->tab_shift(
			function () use ( $declarations, $new_line ): string {
				$tabs  = $this->formatter->tabulate();
				$lines = array();

				foreach ( $declarations as $property => $value ) {
					$lines[] = sprintf( '%s%s: %s;', $tabs, $property, $value );
				}

				return join( $new_line, $lines );
			}
		);

		return join( $new_line, array( $selector . ' {', $lines, '}' ) );
	}
}
