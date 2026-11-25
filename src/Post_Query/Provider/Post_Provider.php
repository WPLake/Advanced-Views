<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Provider;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;

defined( 'ABSPATH' ) || exit;

interface Post_Provider {
	/**
	 * @param string $full_source_id The full unique id of the source, as stored (e.g. the Selection one with the prefix)
	 * @return int[] post IDs in the query order, empty if the source is not found
	 */
	public function get_posts( string $full_source_id, Query_Context $context ): array;
}
