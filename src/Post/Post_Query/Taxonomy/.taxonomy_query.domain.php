<?php

namespace Org\Wplake\Advanced_Views\Post\Post_Query\Taxonomy;

use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Post\Post_Query\Core\post_query_domain;

class taxonomy_query_domain extends post_query_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		field_provider_domain::class,
	];
}
