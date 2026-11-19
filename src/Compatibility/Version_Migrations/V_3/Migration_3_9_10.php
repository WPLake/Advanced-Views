<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Version\Version_Migration_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;

final class Migration_3_9_10 extends Version_Migration_Base {
	const INTRODUCED_VERSION = '3.9.10';

	public function __construct( Logger $logger, Selection_Settings_Storage $post_selections_settings_storage ) {
		parent::__construct( $logger );

		// new Post Selections are query-only, so the existing ones keep their templates explicitly.
		$this->migrations = array(
			new Migration_Has_Template( $logger, $post_selections_settings_storage ),
		);
	}
}
