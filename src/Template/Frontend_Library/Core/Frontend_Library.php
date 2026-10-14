<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

defined( 'ABSPATH' ) || exit;

interface Frontend_Library {
	public static function get_docs_url(): string;
}
