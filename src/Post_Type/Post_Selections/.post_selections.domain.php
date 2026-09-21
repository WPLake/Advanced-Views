<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections;

use Org\Wplake\Advanced_Views\Post_Query\Core\post_query_domain;
use Org\Wplake\Advanced_Views\Post_Query\Selection\selection_query_domain;
use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\layouts_domain;

class post_selections_domain extends post_type_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		layouts_domain::class,
		post_query_domain::class,
		selection_query_domain::class,
	];
}
