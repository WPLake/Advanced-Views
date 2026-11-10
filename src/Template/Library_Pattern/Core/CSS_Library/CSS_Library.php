<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\CSS_Library;

defined( 'ABSPATH' ) || exit;

interface CSS_Library {
	/**
	 * @return string the final CSS code
	 */
	public function get_active_css(): string;
}
