<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;

class Template_Engine_Bootstrap extends Bootstrap_Base {
	public static function get_type_definitions(): array {
		return array(
			Token_Factory_Storage::class        => Engines_Storage::class,
			Template_Renderer_Storage::class    => Engines_Storage::class,
			Template_Integration_Storage::class => Engines_Storage::class,
		);
	}

	public static function get_instance_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Template_Engine_Factory::class );

		return array(
			Engines_Storage::class       => fn() => $factory->engines_storage(),
			Templates_Environment::class => fn() => $factory->templates_environment(),
		);
	}

	public static function get_actor_classes(): array {
		return array( Templates_Environment::class );
	}
}
