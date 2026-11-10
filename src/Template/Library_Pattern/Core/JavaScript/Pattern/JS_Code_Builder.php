<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Pattern;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class JS_Code_Builder {
	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->escaper   = $escaper;
		$this->formatter = $formatter;
	}

	/**
	 * @param class-string<JS_Pattern> $js_pattern
	 */
	public function build( string $js_pattern, string $outer_wrapper ): string {
		$variables                = new JS_Variables();
		$variables->outer_wrapper = $outer_wrapper;

		$options = $js_pattern::get_js_options();
		$setup   = $js_pattern::get_js_setup( $variables );

		$is_filled    = count( $options ) > 0;
		$options_code = $is_filled ?
			$this->formatter->tab_shift(
				fn() => $this->compose_options( $options, $variables->options )
			) :
			'';
		$setup_code   = $this->formatter->tab_shift(
			fn() => $this->compose_statements( $setup )
		);

		$parts = $is_filled ?
			array( $options_code, $setup_code ) :
			array( $setup_code );

		$new_line = $this->formatter->new_line();

		return join( $new_line, $parts );
	}

	/**
	 * @param array<string,mixed> $options
	 */
	protected function compose_options( array $options, string $variable ): string {
		$tabs     = $this->formatter->tabulate();
		$new_line = $this->formatter->new_line();

		$opening = sprintf( '%sconst %s = {', $tabs, $this->escaper->esc_html( $variable ) );
		$rows    = $this->formatter->tab_shift(
			fn() => $this->compose_option_rows( $options )
		);
		$closing = sprintf( '%s};', $tabs );

		$parts = array(
			$opening,
			$rows,
			$closing,
		);

		return join( $new_line, $parts );
	}

	/**
	 * @param array<string,mixed> $options
	 */
	protected function compose_option_rows( array $options ): string {
		$rows = array();

		foreach ( $options as $name => $value ) {
			$rows[] = $this->compose_option( $name, $value );
		}

		$new_line = $this->formatter->new_line();

		return join( $new_line, $rows );
	}

	/**
	 * @param mixed $value
	 */
	protected function compose_option( string $name, $value ): string {
		$is_literal = str_starts_with( $name, '#' );

		if ( $is_literal ) {
			return $this->compose_literal_option( $name, $value );
		}

		$tabs       = $this->formatter->tabulate();
		$json_value = (string) wp_json_encode( $value );

		return sprintf( '%s%s: %s,', $tabs, $this->escaper->esc_html( $name ), $json_value );
	}

	/**
	 * @param mixed $value
	 */
	protected function compose_literal_option( string $name, $value ): string {
		$tabs          = $this->formatter->tabulate();
		$literal_name  = substr( $name, 1 );
		$literal_value = is_scalar( $value ) ?
			(string) $value :
			'';

		return sprintf(
			'%s%s: %s,',
			$tabs,
			$this->escaper->esc_html( $literal_name ),
			$this->escaper->esc_html( $literal_value )
		);
	}

	/**
	 * @param string[] $lines
	 */
	protected function compose_statements( array $lines ): string {
		$tabs       = $this->formatter->tabulate();
		$statements = array();

		foreach ( $lines as $line ) {
			$statements[] = sprintf( '%s%s;', $tabs, $line );
		}

		$new_line = $this->formatter->new_line();

		return join( $new_line, $statements );
	}
}
