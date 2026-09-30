<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Acf\Groups\Tools_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;

defined( 'ABSPATH' ) || exit;

class Tools_Settings_Integration extends Acf_Integration {
	private Layout_Settings_Storage $layouts_settings_storage;
	private Selection_Settings_Storage $post_selections_settings_storage;

	public function __construct( Layout_Settings_Storage $layouts_settings_storage, Selection_Settings_Storage $post_selections_settings_storage ) {
		parent::__construct( '' );

		$this->layouts_settings_storage         = $layouts_settings_storage;
		$this->post_selections_settings_storage = $post_selections_settings_storage;
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Tools_Settings::FIELD_EXPORT_VIEWS =>
				fn() => $this->layouts_settings_storage->get_unique_id_with_name_items_list(),
			Tools_Settings::FIELD_EXPORT_CARDS =>
				fn() => $this->post_selections_settings_storage->get_unique_id_with_name_items_list(),
			Tools_Settings::FIELD_DUMP_VIEWS   =>
				fn() => $this->layouts_settings_storage->get_unique_id_with_name_items_list(),
			Tools_Settings::FIELD_DUMP_CARDS   =>
				fn() => $this->post_selections_settings_storage->get_unique_id_with_name_items_list(),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		Acf_Utils::bind_field_choices( Tools_Settings::class, $choices_callbacks );
	}
}
