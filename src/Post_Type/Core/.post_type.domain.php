<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\dashboard_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\template_engine_domain;

class post_type_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> post_type)
		assets_domain::class,
		// todo break cycle-dependency (dashboard <-> post_type)
		dashboard_domain::class,
		field_provider_domain::class,
		template_engine_domain::class,
	];
	const WHITELIST_NAMESPACES = [
		'Psr\Container',
	];
	const WHITELIST_GLOBALS = [
		'WP_Post',
		'WP_REST_Request',
		'WP_Query',
		'WP_Filesystem_Base',
		'WP_List_Table',
		'WP_Post_Type',
	];
}
