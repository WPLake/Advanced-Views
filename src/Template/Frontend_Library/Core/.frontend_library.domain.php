<?php

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\template_generation_domain;

class frontend_library_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> frontend_library)
		assets_domain::class,
		template_generation_domain::class,
	];
}
