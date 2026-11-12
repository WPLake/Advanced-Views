<?php

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Field_Provider\Field_Types\field_types_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\layout_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\post_selection_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\template_token_domain;

class frontend_library_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> frontend_library)
		assets_domain::class,
		field_provider_domain::class,
		field_types_domain::class,
		layout_cpt_domain::class,
		post_selection_cpt_domain::class,
		template_token_domain::class,
	];
}
