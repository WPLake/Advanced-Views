<?php

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig;

use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\template_engine_domain;

class twig_engine_domain extends template_engine_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		assets_domain::class,
	];
	const WHITELIST_NAMESPACES = [
		...parent::WHITELIST_NAMESPACES,
		'Org\Wplake\Advanced_Views\Vendors\Twig',
	];
}
