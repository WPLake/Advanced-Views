<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Version\Version_Migration;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Version_Migrator;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_1\Migration_1_6_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_1\Migration_1_7_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_0_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_1_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_2_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_2_2;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_2_3;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_3_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_4_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_4_2;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\Migration_2_4_5;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3\Migration_3_0_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3\Migration_3_3_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3\Migration_3_8_0;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3\Migration_3_8_9;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3\Migration_3_9_6;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Vendors\DI\Container;
use function Org\Wplake\Advanced_Views\Utils\resolve_instances;

class Version_Migrations_Bootstrap extends Module_Bootstrap_Base {
	protected Plugin_Cpt $layout_cpt;
	protected Plugin_Cpt $post_selection_cpt;

	public function __construct( Container $container, Plugin_Cpt $layout_cpt, Plugin_Cpt $post_selection_cpt ) {
		parent::__construct( $container );

		$this->layout_cpt         = $layout_cpt;
		$this->post_selection_cpt = $post_selection_cpt;
	}

	/**
	 * @return Hookable[]
	 */
	public function get_hookables( Route_Detector $route_detector ): array {
		/**
		 * Migrations depend on the instances registered in the container by later modules,
		 * so they are resolved lazily, right before the migrator's hooks are set.
		 */
		return array(
			new class( $this ) implements Hookable {
				private Version_Migrations_Bootstrap $bootstrap;

				public function __construct( Version_Migrations_Bootstrap $bootstrap ) {
					$this->bootstrap = $bootstrap;
				}

				public function set_hooks( Route_Detector $route_detector ): void {
					$migrator = $this->bootstrap->register_migrations();
					$migrator->set_hooks( $route_detector );
				}
			},
		);
	}

	public function register_migrations(): Version_Migrator {
		$migrations          = array_merge(
			$this->v1_migrations(),
			$this->v2_migrations(),
			$this->v3_migrations()
		);
		$migration_instances = resolve_instances(
			$migrations,
			$this->container
		);

		$migrator = $this->container->get( Version_Migrator::class );
		$migrator->add_version_migrations( $migration_instances );

		return $migrator;
	}

	/**
	 * @return class-string<Version_Migration>[]
	 */
	protected function v1_migrations(): array {
		return array(
			Migration_1_6_0::class,
			Migration_1_7_0::class,
		);
	}

	/**
	 * @return array<array-key, class-string<Version_Migration>|Version_Migration>
	 */
	protected function v2_migrations(): array {
		return array(
			Migration_2_0_0::class,
			Migration_2_1_0::class,
			Migration_2_2_0::class,
			Migration_2_2_2::class,
			Migration_2_2_3::class,
			Migration_2_3_0::class,
			Migration_2_4_0::class,
			Migration_2_4_2::class,
			Migration_2_4_5::class,
		);
	}

	/**
	 * @return array<array-key, class-string<Version_Migration>|Version_Migration>
	 */
	protected function v3_migrations(): array {
		return array(
			Migration_3_0_0::class,
			Migration_3_3_0::class,
			Migration_3_8_0::class => new Migration_3_8_0(
				$this->container->get( Logger::class ),
				$this->container->get( Layout_Settings_Storage::class ),
				$this->container->get( Selection_Settings_Storage::class ),
				$this->layout_cpt,
				$this->post_selection_cpt
			),
			Migration_3_8_9::class,
			Migration_3_9_6::class,
		);
	}
}
