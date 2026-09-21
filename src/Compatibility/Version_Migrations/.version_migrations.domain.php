<?php

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations;

use Org\Wplake\Advanced_Views\Compatibility\Migration\migration_domain;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_1\v1_namespace;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\v2_namespace;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3\v3_namespace;
use Org\Wplake\Advanced_Views\Field_Provider\Wp\wp_provider_domain;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\layouts_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\post_selections_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\template_engine_domain;

class version_migrations_domain extends migration_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
		template_engine_domain::class,
		// legitimate: migrations do concrete Type-specific stuff (e.g. updating Layout-only fields)
		layouts_cpt_domain::class,
		post_selections_cpt_domain::class,
		// legitimate: migrations do WP-specific stuff (e.g. replace_post_comments_and_menu_link_fields_to_separate)
		wp_provider_domain::class,
	];
	const DECOUPLED_CHILDREN = [
		v1_namespace::class,
		v2_namespace::class,
		v3_namespace::class,
	];
}
