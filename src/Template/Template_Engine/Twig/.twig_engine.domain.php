<?php

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Twig;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\template_engine_domain;

class twig_engine_domain extends template_engine_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
