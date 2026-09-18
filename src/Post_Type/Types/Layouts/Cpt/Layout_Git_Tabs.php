<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Data_Vendors_Base;
use Org\Wplake\Advanced_Views\Plugin\Base\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Settings_Migrator;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Import_Result;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Data_Storage\Layout_Settings_Storage;

class Layout_Git_Tabs extends Git_Tabs {
	private Data_Vendors_Base $data_vendors;
	private Layout_Settings_Storage $layouts_settings_storage;

	public function __construct(
		Cpt_Table $cpt_table,
		Settings_Storage $settings,
		Git_Lab_Api $git_lab_api,
		Cpt_Settings $cpt_settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Cpt_Settings_Migrator $cpt_settings_migrator,
		Data_Vendors_Base $data_vendors,
		Logger $logger
	) {
		parent::__construct(
			$cpt_table,
			$settings,
			$git_lab_api,
			$cpt_settings,
			$layouts_settings_storage,
			$cpt_settings_migrator,
			$data_vendors,
			$logger
		);

		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->data_vendors             = $data_vendors;
	}

	protected function get_cpt_data( string $unique_id ): Cpt_Settings {
		// Views tab has only single storage (unlike Card tab).
		return $this->get_cpt_data_storage()->get( $unique_id );
	}

	protected function import_related_cpt_data_items(
		string $repository_id,
		string $repository_access_token,
		string $unique_id
	): Import_Result {
		$view_data               = $this->layouts_settings_storage->get( $unique_id );
		$related_view_unique_ids = $this->data_vendors->get_related_view_unique_ids( $view_data );

		return $this->import_items(
			$repository_id,
			$repository_access_token,
			$related_view_unique_ids
		);
	}
}
