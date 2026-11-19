<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Migration_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;

/**
 * Turns on the deprecated 'has_template' flag for the existing Post Selections (the new ones are query-only).
 */
final class Migration_Has_Template extends Migration_Base {
	private Cpt_Settings_Storage $cpt_settings_storage;

	public function __construct( Logger $logger, Cpt_Settings_Storage $cpt_settings_storage ) {
		parent::__construct( $logger );

		$this->cpt_settings_storage = $cpt_settings_storage;
	}

	public function migrate(): void {
		File_System_Loader::instance()
			->add_loaded_callback(
				fn() => $this->migrate_items()
			);
	}

	/**
	 * The imports save the item themselves.
	 */
	public function migrate_cpt_settings( Cpt_Settings $cpt_settings ): void {
		self::enable_template( $cpt_settings );
	}

	public function migrate_items(): void {
		foreach ( $this->cpt_settings_storage->get_all() as $cpt_settings ) {
			if ( self::enable_template( $cpt_settings ) ) {
				$this->cpt_settings_storage->save( $cpt_settings );
			}
		}
	}

	/**
	 * @return bool whether the item was changed
	 */
	public static function enable_template( Cpt_Settings $cpt_settings ): bool {
		if ( ! $cpt_settings instanceof Post_Selection_Settings ||
			$cpt_settings->has_template ) {
			return false;
		}

		$cpt_settings->has_template = true;

		return true;
	}
}
