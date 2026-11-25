<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Version\Version_Migration_Base;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Use_Case\Migration_Field_Values;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;

final class Migration_3_9_10 extends Version_Migration_Base {
	const INTRODUCED_VERSION = '3.9.10';

	public function __construct(
		Logger $logger,
		Selection_Settings_Storage $post_selections_settings_storage,
		Layout_Settings_Storage $layouts_settings_storage
	) {
		parent::__construct( $logger );

		/**
		 * New Post Selections are query-only, so the existing ones keep their templates explicitly.
		 * New Layouts are strict about the Item type, so the existing ones get 'custom' to keep the legacy args.
		 */
		$this->migrations = array(
			new Migration_Field_Values(
				$logger,
				$post_selections_settings_storage,
				fn ( Cpt_Settings $cpt_settings ) => $this->enable_template( $cpt_settings )
			),
			new Migration_Field_Values(
				$logger,
				$layouts_settings_storage,
				fn ( Cpt_Settings $cpt_settings ) => $this->set_custom_item_type( $cpt_settings )
			),
		);
	}

	public function enable_template( Cpt_Settings $cpt_settings ): bool {
		if ( ! $cpt_settings instanceof Post_Selection_Settings ||
			$cpt_settings->has_template ) {
			return false;
		}

		$cpt_settings->has_template = true;

		return true;
	}

	public function set_custom_item_type( Cpt_Settings $cpt_settings ): bool {
		if ( ! $cpt_settings instanceof Layout_Settings ||
			Layout_Settings::ITEM_TYPE_CUSTOM === $cpt_settings->item_type ) {
			return false;
		}

		$cpt_settings->item_type = Layout_Settings::ITEM_TYPE_CUSTOM;

		return true;
	}
}
