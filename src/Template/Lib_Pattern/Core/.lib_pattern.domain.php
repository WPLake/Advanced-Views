<?php

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;

class lib_pattern_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> lib_pattern)
		assets_domain::class,
	];
}
