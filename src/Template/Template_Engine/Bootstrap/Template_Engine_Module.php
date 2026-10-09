<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Blade\Blade_Template_Engine;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Twig\Twig_Template_Engine;

class Template_Engine_Module extends Module_Base {
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
			Twig_Template_Engine::class  => fn() => $factory->twig_engine(),
			Blade_Template_Engine::class => fn() => $factory->blade_engine(),
			Templates_Environment::class => fn() => $factory->templates_environment(),
		);
	}

	public static function get_actor_classes(): array {
		return array( Templates_Environment::class );
	}
}
