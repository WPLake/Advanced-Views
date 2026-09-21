<?php

namespace Org\Wplake\Advanced_Views\Post_Query\Entity;

use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Post_Query\Core\post_query_domain;

class entity_domain extends post_query_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		field_provider_domain::class,
	];
}
