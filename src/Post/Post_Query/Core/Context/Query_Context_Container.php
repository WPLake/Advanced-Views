<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post\Post_Query\Core\Context;

defined( 'ABSPATH' ) || exit;

interface Query_Context_Container {
	public function set_query_context( Query_Context $context ): void;
}
