<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Escaping;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

abstract class Html_Escaper_Base implements Html_Escaper {
	/**
	 * @param mixed $value
	 */
	public function stringify( $value ): string {
		if ( $value instanceof Htmlable ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return $value->to_html( $this );
		}

		$text_value = stringExtended( $value );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return $this->esc_html( $text_value );
	}
}
