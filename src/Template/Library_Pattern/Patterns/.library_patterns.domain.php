<?php

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\library_pattern_domain;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\layout_pattern_domain;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection\selection_pattern_domain;

class library_patterns_domain extends library_pattern_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		layout_pattern_domain::class,
		selection_pattern_domain::class,
		...parent::WHITELIST_DOMAINS
	];
}
