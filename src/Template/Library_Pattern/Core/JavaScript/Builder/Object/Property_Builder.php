<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder\Object;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

final class Property_Builder {
	protected Html_Escaper $escaper;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper = $escaper;
	}

	/**
	 * Hash prefix in #name means the value is a JS literal, otherwise it's JSON-encoded.
	 *
	 * @param mixed $value
	 */
	public function build( string $name, $value ): string {
		$is_literal = str_starts_with( $name, '#' );

		if ( $is_literal ) {
			return $this->compose_literal_property( $name, $value );
		}

		return $this->compose_json_property( $name, $value );
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
