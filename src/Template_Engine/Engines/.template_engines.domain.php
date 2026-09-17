<?php

namespace Org\Wplake\Advanced_Views\Template_Engine\Engines;

use Org\Wplake\Advanced_Views\Template_Engine\Core\template_engine_domain;
use Org\Wplake\Advanced_Views\Template_Engine\Engines\Blade\blade_namespace;
use Org\Wplake\Advanced_Views\Template_Engine\Engines\PHP\php_namespace;
use Org\Wplake\Advanced_Views\Template_Engine\Engines\Twig\twig_namespace;

class template_engines_domain extends template_engine_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
	const DECOUPLED_CHILDREN = [
		blade_namespace::class,
		php_namespace::class,
		twig_namespace::class,
	];
	const DECOUPLE_EXCEPTIONS = [
		// blade extends php.
		blade_namespace::class => [ php_namespace::class, ],
	];
}
