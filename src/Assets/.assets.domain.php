<?php

namespace Org\Wplake\Advanced_Views\Assets;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\dashboard_domain;
use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\layouts_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\post_selections_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\library_pattern_domain;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\library_patterns_domain;

class assets_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		field_provider_domain::class,
		// todo break cycle-dependency (dashboard <-> assets)
		dashboard_domain::class,
		// todo break cycle-dependency (post_type <-> assets)
		post_type_domain::class,
		// todo break cycle-dependency (layouts <-> assets)
		layouts_cpt_domain::class,
		// todo break cycle-dependency (selections <-> assets)
		post_selections_cpt_domain::class,
		// todo break cycle-dependency (library_pattern <-> assets)
		library_pattern_domain::class,
		library_patterns_domain::class,
	];
	const WHITELIST_GLOBALS = [
		'WP_Screen',
	];
}
