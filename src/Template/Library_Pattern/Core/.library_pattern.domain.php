<?php

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\layouts_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\post_selections_cpt_domain;

class library_pattern_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> library_pattern)
		assets_domain::class,
		field_provider_domain::class,
		layouts_cpt_domain::class,
		post_selections_cpt_domain::class,
	];
}
