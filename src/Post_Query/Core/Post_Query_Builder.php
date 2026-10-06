<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Query_Settings;

interface Post_Query_Builder {
	/**
	 * @return mixed[]
	 */
	public function build_post_query( Query_Settings $selection_settings ): array;
}
