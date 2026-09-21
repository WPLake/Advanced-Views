<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Migration\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post\Post_Type\Core\Cpt\Cpt_Settings_Migrator;

interface Migration extends Cpt_Settings_Migrator {
	public function migrate(): void;

	public function get_upgrade_notice_text(): ?string;
}
