<?php

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Assets\assets_domain;
use Org\Wplake\Advanced_Views\Compatibility\Migration\migration_domain;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\wp_provider_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\layout_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\post_selection_cpt_domain;

class dashboard_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (assets <-> dashboard)
		assets_domain::class,
		migration_domain::class,
		wp_provider_domain::class,
		// todo break cycle-dependency (layouts <-> dashboard)
		layout_cpt_domain::class,
		// todo break cycle-dependency (selections <-> dashboard)
		post_selection_cpt_domain::class,
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
