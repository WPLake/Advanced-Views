<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\template_engine_domain;

class post_type_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		field_provider_domain::class,
		template_engine_domain::class,
	];
}
