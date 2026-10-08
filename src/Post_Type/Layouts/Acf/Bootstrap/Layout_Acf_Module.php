<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Item_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Layout_Settings_Integration;

class Layout_Acf_Module extends Module_Base {
	public static function get_actor_classes(): array {
		return array(
			Layout_Settings_Integration::class,
			Field_Settings_Integration::class,
			Item_Settings_Integration::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Layout_Acf_Factory::class );

		return array(
			Acf_Groups_Loader::class                => fn() => $factory->groups_loader(),
			Mount_Point_Settings_Integration::class => fn() => $factory->mount_point_integration(),
		);
	}
}
