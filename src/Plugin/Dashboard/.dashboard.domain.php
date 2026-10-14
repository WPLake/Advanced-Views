<?php

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Compatibility\Migration\migration_domain;
use Org\Wplake\Advanced_Views\Field_Provider\Wp\wp_provider_domain;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\layouts_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\post_selections_cpt_domain;

class dashboard_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> dashboard)
		assets_domain::class,
		migration_domain::class,
		wp_provider_domain::class,
		// todo break cycle-dependency (layouts <-> dashboard)
		layouts_cpt_domain::class,
		// todo break cycle-dependency (selections <-> dashboard)
		post_selections_cpt_domain::class,
	];
	const WHITELIST_GLOBALS = [
		'WP_Admin_Bar',
		'WP_Filesystem_Base',
		'WP_Post',
		'WP_Query',
		'WP_REST_Request',
		'WP_Screen',
	];
}
