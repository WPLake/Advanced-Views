<?php

namespace Org\Wplake\Advanced_Views\Template\Library_Concept\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\template_token_domain;

class library_concept_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> library_concept)
		assets_domain::class,
		template_token_domain::class,
	];
}
