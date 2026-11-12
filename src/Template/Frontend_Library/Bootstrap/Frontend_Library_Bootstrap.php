<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;

class Frontend_Library_Bootstrap extends Bootstrap_Base {
	public static function get_instance_factories( Instance_Container $container ): array {
		return array(
			Library_Container::class => fn() => $container->resolve( Frontend_Library_Factory::class )
				->library_container(),
		);
	}
}
