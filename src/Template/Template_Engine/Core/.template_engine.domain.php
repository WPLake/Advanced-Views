<?php

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;

class template_engine_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> template_engine)
		assets_domain::class,
	];
	const WHITELIST_GLOBALS = [
		'WP_Filesystem_Base',
	];
}
