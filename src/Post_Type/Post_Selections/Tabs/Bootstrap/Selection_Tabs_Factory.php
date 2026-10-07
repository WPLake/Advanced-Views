<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Settings_Migrator;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Table;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Post_Selection_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Selection_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;

class Selection_Tabs_Factory extends Container_Facade {
	public function fs_only_tab(): Fs_Only_Tab {
		$cpt_table        = $this->resolve( Post_Selections_Table::class );
		$settings_storage = $this->resolve( Selection_Settings_Storage::class );

		return new Fs_Only_Tab( $cpt_table, $settings_storage );
	}

	public function bulk_validation_tab(): Post_Selections_Bulk_Validation_Tab {
		$cpt_table         = $this->resolve( Post_Selections_Table::class );
		$settings_storage  = $this->resolve( Selection_Settings_Storage::class );
		$fs_only_tab       = $this->fs_only_tab();
		$selection_factory = $this->resolve( Post_Selection_Factory::class );

		return new Post_Selections_Bulk_Validation_Tab( $cpt_table, $settings_storage, $fs_only_tab, $selection_factory );
	}

	public function pre_built_tab(): Post_Selections_Pre_Built_Tab {
		$logger             = $this->resolve( Logger::class );
		$selections_cpt     = $this->resolve( Selections_Cpt::class );
		$plugin             = $this->resolve( Plugin::class );
		$engines_storage    = $this->resolve( Engines_Storage::class );
		$selection_settings = $this->resolve( Post_Selection_Settings::class );
		$provider_cluster   = $this->resolve( Field_Provider_Cluster::class );

		$folder_name    = $selections_cpt->folder_name();
		$pre_built_path = $plugin->get_plugin_path( 'pre_built' );
		$file_system    = new File_System( $logger, $folder_name, $pre_built_path );
		$fs_fields      = new Post_Selection_Fs_Fields( $engines_storage );
		$db_management  = new Db_Management( $logger, $file_system, $selections_cpt, true );

		$pre_built_settings_storage = new Selection_Settings_Storage(
			$logger,
			$file_system,
			$fs_fields,
			$db_management,
			$selection_settings
		);

		$cpt_table             = $this->resolve( Post_Selections_Table::class );
		$settings_storage      = $this->resolve( Selection_Settings_Storage::class );
		$migrator              = $this->resolve( Cpt_Settings_Migrator::class );
		$layouts_pre_built_tab = $this->resolve( Layouts_Pre_Built_Tab::class );

		return new Post_Selections_Pre_Built_Tab(
			$cpt_table,
			$settings_storage,
			$pre_built_settings_storage,
			$provider_cluster,
			$migrator,
			$logger,
			$layouts_pre_built_tab
		);
	}
}
