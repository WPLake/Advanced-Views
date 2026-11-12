<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Field_Provider\Field_Types\field_types_domain;
use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\layout_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\frontend_libraries_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\template_token_domain;

class field_provider_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		field_types_domain::class,
		template_token_domain::class,
		// todo break cycle-dependency
		post_type_domain::class,
		frontend_libraries_domain::class,
		// todo break concrete dependency.
		layout_cpt_domain::class,
	];
	const WHITELIST_GLOBALS = [
		'WP_Comment',
		'WP_Post',
		'WP_Term',
		'WP_User',
	];
}
