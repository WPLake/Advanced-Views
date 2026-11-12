<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections;

use Org\Wplake\Advanced_Views\Post_Query\Core\post_query_domain;
use Org\Wplake\Advanced_Views\Post_Query\Selection\selection_query_domain;
use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Integration\cpt_integration_domain;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\layouts_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\layout_library_adapter_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\frontend_library_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\template_token_domain;

class post_selections_cpt_domain extends post_type_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		layouts_cpt_domain::class,
		post_query_domain::class,
		selection_query_domain::class,
		frontend_library_domain::class,
		layout_library_adapter_domain::class,
		template_token_domain::class,
		cpt_integration_domain::class,
	];
	const WHITELIST_NAMESPACES = [
		...parent::WHITELIST_NAMESPACES,
		'Elementor',
		'Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups',
	];
}
