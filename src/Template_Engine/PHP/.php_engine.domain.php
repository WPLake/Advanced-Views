<?php

namespace Org\Wplake\Advanced_Views\Template_Engine\PHP;

use Org\Wplake\Advanced_Views\Template_Engine\Core\template_engine_domain;

class php_engine_domain extends template_engine_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
