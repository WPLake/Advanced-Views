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

class Version_Migrations_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookable( Route_Detector $route_detector ): Hookable {
		$migration_classes = array_merge(
			$this->v1_migrations(),
			$this->v2_migrations(),
			$this->v3_migrations()
		);
		$migrations        = array_map(
			fn( string $migration_class ): Version_Migration => $this->container->get( $migration_class ),
			$migration_classes
		);

		$migrator = $this->container->get( Version_Migrator::class );
		$migrator->add_version_migrations( $migrations );

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
	 * @return class-string<Version_Migration>[]
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
	 * @return class-string<Version_Migration>[]
	 */
	protected function v3_migrations(): array {
		return array(
			Migration_3_0_0::class,
			Migration_3_3_0::class,
			Migration_3_8_0::class,
			Migration_3_8_9::class,
			Migration_3_9_6::class,
		);
	}
}
