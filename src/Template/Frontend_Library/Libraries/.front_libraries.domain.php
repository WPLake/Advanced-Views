<?php

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\frontend_library_domain;

class front_libraries_domain extends frontend_library_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
