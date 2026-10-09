<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Dependency;
use Org\Wplake\Advanced_Views\Acf\Acf_Internal_Features;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Custom_Acf_Field_Types;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Tools_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Groups\Git_Repository;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;

class Acf_Bootstrap extends Bootstrap_Base {
	public static function get_type_definitions(): array {
		return array( CreatorInterface::class => Creator::class );
	}

	public static function get_instance_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Acf_Factory::class );

		return array(
			Git_Repository::class => fn() => $factory->git_repository(),
		);
	}

	public static function get_actor_classes(): array {
		return array(
			Acf_Dependency::class,
			Acf_Internal_Features::class,
			Tools_Settings_Integration::class,
			Custom_Acf_Field_Types::class,
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Acf_Factory::class );

		return array(
			Acf_Groups_Loader::class => fn() => $factory->groups_loader(),
		);
	}
}
