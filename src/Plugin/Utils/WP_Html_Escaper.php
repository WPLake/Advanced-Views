<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils;

use Org\Wplake\Advanced_Views\Template\Html\Html_Escaper;

defined( 'ABSPATH' ) || exit;

final class WP_Html_Escaper implements Html_Escaper {
	public function esc_html( string $text ): string {
		return esc_html( $text );
	}

	public function esc_url( string $url ): string {
		return esc_url( $url );
	}
}
