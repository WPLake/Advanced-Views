<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;

class Html_Bootstrap extends Bootstrap_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Html_Renderer::class => fn() => $container->resolve( Html_Factory::class )
														->html_renderer(),
		);
	}
}
