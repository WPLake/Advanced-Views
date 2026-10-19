<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Integration\cpt_integration_domain;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\post_selections_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\library_pattern_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\template_generation_domain;

class layouts_cpt_domain extends post_type_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		library_pattern_domain::class,
		template_generation_domain::class,
		cpt_integration_domain::class,
		post_selections_cpt_domain::class,
	];
	const WHITELIST_NAMESPACES = [
		...parent::WHITELIST_NAMESPACES,
		'Elementor',
		'Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups',
	];
	const WHITELIST_GLOBALS = [
		...parent::WHITELIST_GLOBALS,
		'WP_Block',
		'WP_Block_Template',
		'WP_Comment',
		'WP_Term',
	];
}
