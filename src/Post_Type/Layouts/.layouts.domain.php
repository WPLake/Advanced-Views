<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;

class layouts_domain extends post_type_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
