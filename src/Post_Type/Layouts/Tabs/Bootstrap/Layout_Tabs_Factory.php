<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Facade;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Settings_Migrator;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;

class Layout_Tabs_Factory extends Factory_Facade {
	public function fs_only_tab(): Fs_Only_Tab {
		$cpt_table        = $this->resolve( Layouts_Cpt_Table::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );

		return new Fs_Only_Tab( $cpt_table, $settings_storage );
	}

	public function bulk_validation_tab(): Layouts_Bulk_Validation_Tab {
		$cpt_table        = $this->resolve( Layouts_Cpt_Table::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$fs_only_tab      = $this->fs_only_tab();
		$layout_factory   = $this->resolve( Layout_Factory::class );

		return new Layouts_Bulk_Validation_Tab( $cpt_table, $settings_storage, $fs_only_tab, $layout_factory );
	}

	public function pre_built_tab(): Layouts_Pre_Built_Tab {
		$logger           = $this->resolve( Logger::class );
		$layout_cpt       = $this->resolve( Layouts_Cpt::class );
		$plugin           = $this->resolve( Plugin::class );
		$engines_storage  = $this->resolve( Engines_Storage::class );
		$layout_settings  = $this->resolve( Layout_Settings::class );
		$provider_cluster = $this->resolve( Field_Provider_Cluster::class );

		$folder_name    = $layout_cpt->folder_name();
		$pre_built_path = $plugin->get_plugin_path( 'pre_built' );
		$file_system    = new File_System( $logger, $folder_name, $pre_built_path );
		$fs_fields      = new Layout_Fs_Fields( $engines_storage );
		$db_management  = new Db_Management( $logger, $file_system, $layout_cpt, true );

		$pre_built_settings_storage = new Layout_Settings_Storage(
			$logger,
			$file_system,
			$fs_fields,
			$db_management,
			$layout_settings
		);

		$cpt_table        = $this->resolve( Layouts_Cpt_Table::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$migrator         = $this->resolve( Cpt_Settings_Migrator::class );

		return new Layouts_Pre_Built_Tab(
			$cpt_table,
			$settings_storage,
			$pre_built_settings_storage,
			$provider_cluster,
			$migrator,
			$logger
		);
	}
}
