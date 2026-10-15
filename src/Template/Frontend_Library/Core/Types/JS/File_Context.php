<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS;

defined( 'ABSPATH' ) || exit;

interface File_Context {
	public function get_google_map_api_key(): string;
}
