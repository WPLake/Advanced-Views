<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Bootstrap;

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
	 * @var string[]
	 */
	private array $target_cpts;

	/**
	 * @param array<string,string> $namespace_to_path groups namespace => directory path
	 * @param string[] $target_cpts CPT names, whose admin routes need the groups (ajax requests always do)
	 */
	public function __construct( array $namespace_to_path, array $target_cpts ) {
		$this->namespace_to_path = $namespace_to_path;
		$this->target_cpts       = $target_cpts;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route() ||
				wp_doing_ajax();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		$is_target_route = $this->is_target_cpt_route( $route_detector ) ||
							wp_doing_ajax();

		if ( $is_target_route ) {
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

	protected function is_target_cpt_route( Route_Detector $route_detector ): bool {
		foreach ( $this->target_cpts as $cpt_name ) {
			if ( $route_detector->is_cpt_admin_route( $cpt_name ) ) {
				return true;
			}
		}

		return false;
	}
}
