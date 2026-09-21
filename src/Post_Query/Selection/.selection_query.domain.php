<?php

namespace Org\Wplake\Advanced_Views\Post_Query\Selection;

use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Post_Query\Core\post_query_domain;
use Org\Wplake\Advanced_Views\Post_Query\Entity\entity_domain;
use Org\Wplake\Advanced_Views\Post_Query\Taxonomy\taxonomy_domain;

class selection_query_domain extends post_query_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		field_provider_domain::class,
		entity_domain::class,
		taxonomy_domain::class,
	];
}
