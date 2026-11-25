<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Provider;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;

defined( 'ABSPATH' ) || exit;

interface Post_Provider {
	/**
	 * @return int[] post IDs in the query order, empty if the source isn't found
	 */
	public function get_posts( string $source_id, Query_Context $context ): array;
}
