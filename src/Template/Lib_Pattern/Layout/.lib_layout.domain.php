<?php

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Layout;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\layouts_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\lib_pattern_domain;

class lib_layout_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		lib_pattern_domain::class,
		field_provider_domain::class,
		layouts_cpt_domain::class,
	];
}
