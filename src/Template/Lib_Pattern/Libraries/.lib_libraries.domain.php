<?php

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Libraries;

use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\lib_pattern_domain;

class lib_libraries_domain extends lib_pattern_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
