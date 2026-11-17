<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper_Base;

defined( 'ABSPATH' ) || exit;

final class WP_Html_Escaper extends Html_Escaper_Base {
	public function esc_html( string $text ): string {
		return esc_html( $text );
	}

	public function esc_url( string $url ): string {
		return esc_url( $url );
	}
}
