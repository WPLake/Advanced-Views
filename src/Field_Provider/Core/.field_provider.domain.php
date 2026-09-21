<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Post\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post\Post_Type\Layouts\layouts_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\library_patterns_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\template_engine_domain;

class field_provider_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		template_engine_domain::class,
		// todo break cycle-dependency
		post_type_domain::class,
		library_patterns_domain::class,
		// todo break concrete dependency.
		layouts_cpt_domain::class,
	];
}
