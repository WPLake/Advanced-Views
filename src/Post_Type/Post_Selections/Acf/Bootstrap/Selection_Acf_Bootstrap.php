<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Meta_Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Post_Selection_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Tax_Field_Settings_Integration;

class Selection_Acf_Bootstrap extends Bootstrap_Base {
	public static function get_actor_classes(): array {
		return array(
			Post_Selection_Settings_Integration::class,
			// metaField is a part of the Meta Filter, so we use the selection CPT here.
			Meta_Field_Settings_Integration::class,
			Tax_Field_Settings_Integration::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Selection_Acf_Factory::class );

		return array(
			Acf_Groups_Loader::class                => fn() => $factory->groups_loader(),
			Mount_Point_Settings_Integration::class => fn() => $factory->mount_point_integration(),
		);
	}
}
