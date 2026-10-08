<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\Bootstrap;

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
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Facade\Factory_Facade;

final class Version_Migrations_Factory extends Factory_Facade {
	public function register_migrations(): Version_Migrator {
		$v1_migrations = $this->v1_migrations();
		$v2_migrations = $this->v2_migrations();
		$v3_migrations = $this->v3_migrations();

		$migrations          = array_merge( $v1_migrations, $v2_migrations, $v3_migrations );
		$migration_instances = array_map(
			fn( string $class_name ): Version_Migration => $this->resolve( $class_name ),
			$migrations
		);

		$migrator = $this->resolve( Version_Migrator::class );
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
