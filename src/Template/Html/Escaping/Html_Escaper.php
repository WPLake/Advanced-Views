<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Escaping;

defined( 'ABSPATH' ) || exit;

interface Html_Escaper {
	public function esc_html( string $text ): string;

	public function esc_url( string $url ): string;

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function esc_attr( $value ): string;

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function to_safe_string( $value ): string;
}
