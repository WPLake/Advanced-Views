<?php

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;

class library_pattern_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		field_provider_domain::class,
	];
}
