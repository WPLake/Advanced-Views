<?php

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\library_pattern_domain;

class selection_pattern_domain extends library_pattern_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
