<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Integration;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\cpt_integration_namespace;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\elementor_integration_namespace;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Gutenberg\gutenberg_integration_namespace;

class cpt_integration_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		post_type_domain::class,
	];
	const DECOUPLED_CHILDREN = [
		cpt_integration_namespace::class,
		elementor_integration_namespace::class,
		gutenberg_integration_namespace::class,
	];
	const DECOUPLE_EXCEPTIONS = [
		elementor_integration_namespace::class => [ cpt_integration_namespace::class ],
		gutenberg_integration_namespace::class => [ cpt_integration_namespace::class ],
	];
}
