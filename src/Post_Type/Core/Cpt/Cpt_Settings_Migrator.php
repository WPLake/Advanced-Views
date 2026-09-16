<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;

interface Cpt_Settings_Migrator {
	public function migrate_cpt_settings( Cpt_Settings $cpt_settings ): void;
}
