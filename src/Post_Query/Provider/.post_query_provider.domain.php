<?php

namespace Org\Wplake\Advanced_Views\Post_Query\Provider;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Post_Query\Core\post_query_domain;

class post_query_provider_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		post_query_domain::class,
	];
}
