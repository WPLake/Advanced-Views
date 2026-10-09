<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders\Lite;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Upgrade_Notice;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Loaders\Plugin_Loader_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Options_Storage;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\Cache_Flusher;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Api_Interface;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;

final class Lite_Plugin_Loader extends Plugin_Loader_Base {
	public Options_Storage $options;

	public string $plugin_file;

	public function __construct( string $plugin_file ) {
		parent::__construct();

		$this->plugin_file = $plugin_file;
	}

	/**
	 * @return Actor[]
	 */
	protected function primary(): array {
		$layout_cpt    = $this->resolve( Layouts_Cpt::class );
		$selection_cpt = $this->resolve( Selections_Cpt::class );

		$this->options  = $this->resolve( Options_Storage::class );
		$this->settings = $this->resolve( Settings_Storage::class );
		$this->wire( Cpt_Theme_Settings::class, $this->settings );

		$uploads_folder = Plugin::uploads_folder();
		$this->logger   = new Logger( $uploads_folder, $this->settings );
		$this->wire( Logger::class, $this->logger );

		$this->group_creator = new Creator();
		$this->wire( Creator::class, $this->group_creator );
		$this->wire( CreatorInterface::class, $this->group_creator );


		$this->post_selections_settings_storage = $this->resolve( Selection_Settings_Storage::class );
		$post_selections_file_system            = $this->post_selections_settings_storage->get_file_system();

		$this->layouts_settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$layouts_file_system            = $this->layouts_settings_storage->get_file_system();

		$this->plugin = new Plugin( $this->plugin_file, $this->options, $this->settings );
		$this->wire( Plugin::class, $this->plugin );

		$this->git_lab_api = new Git_Lab_Api(
			$this->logger,
			$this->options,
			$layout_cpt,
			$selection_cpt
		);
		$this->wire( Git_Lab_Api::class, $this->git_lab_api );
		$this->wire( Git_Api_Interface::class, $this->git_lab_api );
		$this->upgrade_notice = $this->resolve( Upgrade_Notice::class );
		$this->cache_flusher  = new Cache_Flusher( $this->logger, $this->get_cache_cleaners() );
		$this->wire( Cache_Flusher::class, $this->cache_flusher );

		$this->add_file_systems(
			array(
				$layouts_file_system,
				$post_selections_file_system,
			)
		);

		return parent::primary();
	}

	/**
	 * @return Actor[]
	 */
	protected function others(): array {
		$layout_cpt    = $this->resolve( Layouts_Cpt::class );
		$selection_cpt = $this->resolve( Selections_Cpt::class );

		$this->point_mounter = new Point_Mounter(
			array(
				new Point_Provider( $this->layouts_settings_storage, $layout_cpt ),
				new Point_Provider( $this->post_selections_settings_storage, $selection_cpt ),
			)
		);

		return parent::others();
	}
}
