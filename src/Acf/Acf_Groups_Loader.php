<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf;

use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Loader;

defined( 'ABSPATH' ) || exit;

final class Acf_Groups_Loader implements Hookable {
	/**
	 * @var array<string,string> groups namespace => directory path
	 */
	private array $namespace_to_path;

	/**
	 * @param array<string,string> $namespace_to_path groups namespace => directory path
	 */
	public function __construct( array $namespace_to_path ) {
		$this->namespace_to_path = $namespace_to_path;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		Hookable_Base::add_action(
			'acf/init',
			function (): void {
				$loader = new Loader();

				foreach ( $this->namespace_to_path as $namespace => $path ) {
					$loader->signUpGroups( $namespace, $path );
				}
			},
			// make sure it's after translations.
			9
		);
	}
}
