<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\State_Report;
use Org\Wplake\Advanced_Views\Plugin\Automated_Reports\Usage_Report;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Environment;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Translations;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;

class Plugin_Factory extends Factory_Base {
	public function usage_report(): Usage_Report {
		return new Usage_Report(
			$this->resolve( Logger::class ),
			$this->resolve( Plugin::class ),
			$this->resolve( Settings_Storage::class ),
			$this->resolve( State_Report::class ),
			$this->settings_storages()
		);
	}

	public function plugin_translations(): Plugin_Translations {
		$plugin              = $this->resolve( Plugin::class );
		$lang_relative_paths = array( 'acf-views' => 'lang' );

		return new Plugin_Translations( $plugin, $lang_relative_paths );
	}

	public function plugin_environment(): Plugin_Environment {
		$storages     = $this->settings_storages();
		$file_systems = array_map(
			fn( $storage ) => $storage->get_file_system(),
			$storages
		);

		return new Plugin_Environment(
			$this->resolve( Templates_Environment::class ),
			$this->resolve( State_Report::class ),
			$this->resolve( Usage_Report::class ),
			$this->resolve( Settings_Storage::class ),
			$this->resolve( Plugin::class ),
			$this->resolve( Post_Selections_Pre_Built_Tab::class ),
			$file_systems,
			$storages
		);
	}

	/**
	 * @return array{Layout_Settings_Storage,Selection_Settings_Storage}
	 */
	protected function settings_storages(): array {
		return array(
			$this->resolve( Layout_Settings_Storage::class ),
			$this->resolve( Selection_Settings_Storage::class ),
		);
	}
}
