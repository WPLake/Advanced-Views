<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;

defined( 'ABSPATH' ) || exit;

interface Library_Pattern {
	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	public function generate_code( Cpt_Settings $cpt_settings ): array;

	public function maybe_activate( Cpt_Settings $cpt_settings ): void;
}
