<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Version\Version_Migration_Base;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Use_Case\Migration_Fs_Field;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Use_Case\Migration_Post_Type;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;

final class Migration_3_8_0 extends Version_Migration_Base {
	const INTRODUCED_VERSION = '3.8.0';
	const ORDER              = self::ORDER_BEFORE_ALL;

	public function __construct(
		Logger $logger,
		Layout_Settings_Storage $view_cpt_settings_storage,
		Selection_Settings_Storage $card_cpt_settings_storage,
		Layouts_Cpt $layouts_cpt,
		Selections_Cpt $selections_cpt
	) {
		parent::__construct( $logger );

		$file_system      = $view_cpt_settings_storage->get_file_system();
		$this->migrations = array(
			new Migration_Post_Type( $logger, $view_cpt_settings_storage, $this->get_views_cpt(), $layouts_cpt ),
			new Migration_Post_Type( $logger, $card_cpt_settings_storage, $this->get_cards_cpt(), $selections_cpt ),
			new Migration_Fs_Field( $logger, $file_system, 'view.php', 'controller.php' ),
			new Migration_Fs_Field( $logger, $file_system, 'card.php', 'controller.php' ),
		);
	}

	public function get_upgrade_notice_text(): string {
		return __(
			'Views are now called Layouts and Cards are called Post Selections. Same great features, just easier to use!',
			'acf-views'
		);
	}

	protected function get_views_cpt(): Plugin_Cpt {
		return $this->make_cpt( 'acf_views', 'view_', 'views' );
	}

	protected function get_cards_cpt(): Plugin_Cpt {
		return $this->make_cpt( 'acf_cards', 'card_', 'cards' );
	}

	protected function make_cpt( string $cpt_name, string $slug_prefix, string $folder_name ): Plugin_Cpt {
		return new class( $cpt_name, $slug_prefix, $folder_name ) extends Plugin_Cpt_Base {
			public function __construct( string $cpt_name, string $slug_prefix, string $folder_name ) {
				$this->cpt_name    = $cpt_name;
				$this->slug_prefix = $slug_prefix;
				$this->folder_name = $folder_name;
			}
		};
	}
}
