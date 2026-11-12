<?php

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\frontend_library_domain;

class layout_library_adapter_domain extends frontend_library_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
