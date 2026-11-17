<?php

namespace Org\Wplake\Advanced_Views\Assets;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\dashboard_domain;
use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\layout_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\post_selection_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\layout_library_adapter_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\selection_library_adapter_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\frontend_library_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\frontend_libraries_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\template_token_domain;

class assets_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		field_provider_domain::class,
		// todo break cycle-dependency (dashboard <-> assets)
		dashboard_domain::class,
		// todo break cycle-dependency (post_type <-> assets)
		post_type_domain::class,
		// todo break cycle-dependency (layouts <-> assets)
		layout_cpt_domain::class,
		// todo break cycle-dependency (selections <-> assets)
		post_selection_cpt_domain::class,
		// todo break cycle-dependency (frontend_library <-> assets)
		frontend_library_domain::class,
		layout_library_adapter_domain::class,
		selection_library_adapter_domain::class,
		frontend_libraries_domain::class,
		template_token_domain::class,
	];
	const WHITELIST_GLOBALS = [
		'WP_Screen',
	];
}
