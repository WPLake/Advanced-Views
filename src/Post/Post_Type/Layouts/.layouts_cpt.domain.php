<?php

namespace Org\Wplake\Advanced_Views\Post\Post_Type\Layouts;

use Org\Wplake\Advanced_Views\Post\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\library_pattern_domain;

class layouts_cpt_domain extends post_type_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		library_pattern_domain::class,
	];
}
