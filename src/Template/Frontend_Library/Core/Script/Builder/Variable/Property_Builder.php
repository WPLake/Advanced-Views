<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class Property_Builder {
	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->escaper   = $escaper;
		$this->formatter = $formatter;
	}

	/**
	 * The property line at the current depth.
	 * Hash prefix in #name means the value is a JS literal, otherwise it's JSON-encoded.
	 *
	 * @param mixed $value
	 */
	public function build_property( string $name, $value ): string {
		$tabs       = $this->formatter->tabulate();
		$is_literal = 0 === strpos( $name, '#' );
		$property   = $is_literal ?
			$this->compose_literal_property( $name, $value ) :
			$this->compose_json_property( $name, $value );

		return sprintf( '%s%s', $tabs, $property );
	}

	/**
	 * @param mixed $value
	 */
	protected function compose_literal_property( string $name, $value ): string {
		$literal_name  = substr( $name, 1 );
		$literal_value = is_scalar( $value ) ?
			(string) $value :
			'';

		return sprintf(
			'%s: %s,',
			$this->escaper->esc_html( $literal_name ),
			$this->escaper->esc_html( $literal_value )
		);
	}

	/**
	 * @param mixed $value
	 */
	protected function compose_json_property( string $name, $value ): string {
		$json_value = (string) wp_json_encode( $value );

		return sprintf( '%s: %s,', $this->escaper->esc_html( $name ), $json_value );
	}
}
