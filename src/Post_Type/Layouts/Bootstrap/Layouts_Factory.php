<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Db_Management;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Fs_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

class Layouts_Factory extends Factory_Base {
	public function editor_settings(): Cpt_Gutenberg_Editor_Settings {
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$cpt_name    = $layouts_cpt->cpt_name();

		return new Cpt_Gutenberg_Editor_Settings( $cpt_name );
	}

	public function assets_reducer(): Cpt_Assets_Reducer {
		$settings_storage = $this->resolve( Settings_Storage::class );
		$plugin           = $this->resolve( Plugin::class );
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );
		$cpt_name         = $layouts_cpt->cpt_name();

		return new Cpt_Assets_Reducer( $settings_storage, $plugin, $cpt_name );
	}

	public function layout_settings(): Layout_Settings {
		return $this->resolve( Creator::class )
					->create( Layout_Settings::class );
	}

	public function item_settings(): Item_Settings {
		return $this->resolve( Creator::class )
					->create( Item_Settings::class );
	}

	public function repeater_field_settings(): Repeater_Field_Settings {
		return $this->resolve( Creator::class )
					->create( Repeater_Field_Settings::class );
	}

	public function settings_storage(): Layout_Settings_Storage {
		$logger      = $this->resolve( Logger::class );
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$file_system = new File_System( $logger, $layouts_cpt->folder_name() );

		return new Layout_Settings_Storage(
			$logger,
			$file_system,
			$this->resolve( Layout_Fs_Fields::class ),
			new Db_Management( $logger, $file_system, $layouts_cpt ),
			$this->resolve( Layout_Settings::class )
		);
	}
}
