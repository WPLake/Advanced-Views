<?php

namespace Org\Wplake\Advanced_Views\Template_Engine\Blade;

use Org\Wplake\Advanced_Views\Template_Engine\PHP\php_domain;

// blade extends php.
class blade_domain extends php_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
