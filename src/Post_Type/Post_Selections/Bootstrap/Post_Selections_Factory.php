<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Post_Selection_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

class Post_Selections_Factory extends Factory_Base {
	public function editor_settings(): Cpt_Gutenberg_Editor_Settings {
		$selections_cpt = $this->resolve( Selections_Cpt::class );
		$cpt_name       = $selections_cpt->cpt_name();

		return new Cpt_Gutenberg_Editor_Settings( $cpt_name );
	}

	public function assets_reducer(): Cpt_Assets_Reducer {
		$settings_storage = $this->resolve( Settings_Storage::class );
		$plugin           = $this->resolve( Plugin::class );
		$selections_cpt   = $this->resolve( Selections_Cpt::class );
		$cpt_name         = $selections_cpt->cpt_name();

		return new Cpt_Assets_Reducer( $settings_storage, $plugin, $cpt_name );
	}

	public function selection_settings(): Post_Selection_Settings {
		return $this->resolve( Creator::class )
					->create( Post_Selection_Settings::class );
	}

	public function settings_storage(): Selection_Settings_Storage {
		$logger         = $this->resolve( Logger::class );
		$selections_cpt = $this->resolve( Selections_Cpt::class );
		$file_system    = new File_System( $logger, $selections_cpt->folder_name() );

		return new Selection_Settings_Storage(
			$logger,
			$file_system,
			$this->resolve( Post_Selection_Fs_Fields::class ),
			new Db_Management( $logger, $file_system, $selections_cpt ),
			$this->resolve( Post_Selection_Settings::class )
		);
	}
}
