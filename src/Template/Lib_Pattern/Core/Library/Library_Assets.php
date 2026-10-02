<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library;

use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Assets\Active_Libraries;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Assets\Assets_Handles;
defined( 'ABSPATH' ) || exit;

interface Library_Assets extends Library {
	public function get_handles(): Assets_Handles;

	/**
	 * @return string CSS code to be printed inline
	 */
	public function enqueue_active( Active_Libraries $active_libraries ): string;
}
