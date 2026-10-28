<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Escaper;

defined( 'ABSPATH' ) || exit;

interface Html_Escaper {
	public function esc_html( string $text ): string;

	public function esc_url( string $url ): string;

	public function esc_attr( string $name, string $value ): string;

	/**
	 * Returns safe to use HTML string.
	 *
	 * @param mixed $value
	 */
	public function stringify( $value ): string;
}
