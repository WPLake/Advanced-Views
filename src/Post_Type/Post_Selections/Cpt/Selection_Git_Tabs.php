<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Import_Result;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Api_Interface;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Version_Migrator;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Table;


class Selection_Git_Tabs extends Git_Tabs {
	private Selection_Settings_Storage $post_selections_settings_storage;
	private Layout_Git_Tabs $layouts_git_cpt_table_tabs;

	public function __construct(
		Post_Selections_Table $cpt_table,
		Settings_Storage $settings,
		Git_Api_Interface $git_api,
		Post_Selection_Settings $cpt_settings,
		Selection_Settings_Storage $post_selections_settings_storage,
		Version_Migrator $cpt_settings_migrator,
		Layout_Git_Tabs $layouts_git_cpt_table_tabs,
		Field_Provider_Cluster $provider_cluster,
		Logger $logger
	) {
		parent::__construct(
			$cpt_table,
			$settings,
			$git_api,
			$cpt_settings,
			$post_selections_settings_storage,
			$cpt_settings_migrator,
			$provider_cluster,
			$logger
		);

		$this->post_selections_settings_storage = $post_selections_settings_storage;
		$this->layouts_git_cpt_table_tabs       = $layouts_git_cpt_table_tabs;
	}

	protected function get_cpt_data( string $unique_id ): Cpt_Settings {
		return 0 === strpos( $unique_id, Layout_Settings::UNIQUE_ID_PREFIX ) ?
			$this->layouts_git_cpt_table_tabs->get_cpt_data( $unique_id ) :
			$this->post_selections_settings_storage->get( $unique_id );
	}

	protected function import_related_cpt_data_items(
		string $repository_id,
		string $repository_access_token,
		string $unique_id
	): Import_Result {
		$card_data = $this->post_selections_settings_storage->get( $unique_id );

		return $this->layouts_git_cpt_table_tabs->import_items(
			$repository_id,
			$repository_access_token,
			array( $card_data->acf_view_id )
		);
	}

	protected function get_unique_id_prefix(): string {
		return Post_Selection_Settings::UNIQUE_ID_PREFIX;
	}

	protected function is_layout_item( Cpt_Settings $cpt_settings ): bool {
		return $cpt_settings instanceof Layout_Settings;
	}

	protected function get_title_field_name(): string {
		return Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_TITLE );
	}
}
