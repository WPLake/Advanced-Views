<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Version\Version_Migration_Base;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Use_Case\Migration_Fallback_Fields;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;

final class Migration_3_9_9 extends Version_Migration_Base {
	const INTRODUCED_VERSION = '3.9.9';

	public function __construct(
		Logger $logger,
		Layout_Settings_Storage $layouts_settings_storage,
		Selection_Settings_Storage $post_selections_settings_storage
	) {
		parent::__construct( $logger );

		// script.js is JSON-only now, so move the existing files into the JSON.
		$this->migrations = array(
			new Migration_Fallback_Fields( $logger, $layouts_settings_storage ),
			new Migration_Fallback_Fields( $logger, $post_selections_settings_storage ),
		);
	}
}
