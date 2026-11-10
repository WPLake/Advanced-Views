<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\CSS_Library;

defined( 'ABSPATH' ) || exit;

final class CSS_Library_Loader {
	private string $css_code;

	public function __construct() {
		$this->css_code = '';
	}

	public function load_pattern( string $name, CSS_Library $pattern ): void {
		$css_code  = $pattern->get_active_css();
		$is_filled = strlen( $css_code ) > 0;

		if ( $is_filled ) {
			// no escaping, it's a CSS code, so e.g '.a > .b' shouldn't be escaped.
			$this->css_code .= sprintf( "/*%s*/\n%s\n", $name, $css_code );
		}
	}

	public function get_css_code(): string {
		return $this->css_code;
	}
}
