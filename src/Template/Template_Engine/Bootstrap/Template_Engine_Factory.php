<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Blade\Blade_Template_Engine;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Template_Engine;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Twig\Twig_Template_Engine;

final class Template_Engine_Factory extends Factory_Base {
	public function engines_storage(): Engines_Storage {
		$engines = array_map(
			fn( string $class_name ): Template_Engine => $this->resolve( $class_name ),
			$this->engine_classes()
		);

		return new Engines_Storage(
			$engines,
			$this->resolve( Twig_Template_Engine::class ),
			Plugin::uploads_folder()
		);
	}

	public function templates_environment(): Templates_Environment {
		return new Templates_Environment(
			Plugin::uploads_folder(),
			$this->resolve( Logger::class ),
			$this->resolve( Plugin::class )
		);
	}

	/**
	 * @return class-string<Template_Engine>[]
	 */
	protected function engine_classes(): array {
		return array(
			Twig_Template_Engine::class,
			Blade_Template_Engine::class,
			PHP_Template_Engine::class,
		);
	}
}
