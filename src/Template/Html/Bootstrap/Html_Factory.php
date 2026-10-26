<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;

final class Html_Factory extends Factory_Base {
	public function html_renderer(): Html_Renderer {
		$wp_escaper = $this->resolve( WP_Html_Escaper::class );

		return new Html_Renderer( $wp_escaper );
	}
}
