<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\post_types_domain;
use Org\Wplake\Advanced_Views\Template_Engine\Core\template_engine_domain;

class field_provider_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		template_engine_domain::class,
		// todo break cycle-dependency
		post_type_domain::class,
		// todo break concrete dependency.
		post_types_domain::class,
	];
}
