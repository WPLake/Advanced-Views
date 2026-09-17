<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Template_Engine\Core\template_engine_domain;

class field_provider_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		template_engine_domain::class,
	];
}

return field_provider_domain::class;
