<?php

namespace Org\Wplake\Advanced_Views\Library_Pattern\Patterns;

use Org\Wplake\Advanced_Views\Library_Pattern\Core\library_pattern_domain;

class library_patterns_domain extends library_pattern_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS
	];
}
