<?php

namespace Org\Wplake\Advanced_Views\Post_Query\Core;

use Architecture\Policy\Domain_Policy;

class post_query_domain extends Domain_Policy {
	const WHITELIST_GLOBALS = [
		'WP_Query',
	];
}
