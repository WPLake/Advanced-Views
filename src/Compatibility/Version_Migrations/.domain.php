<?php

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations;

use Org\Wplake\Advanced_Views\Compatibility\Migration\migration_domain;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_1\v1_namespace;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2\v2_namespace;
use Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_3\v3_namespace;

class version_migrations_domain extends migration_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
	const DECOUPLED_CHILDREN = [
		v1_namespace::class,
		v2_namespace::class,
		v3_namespace::class,
	];
}

return version_migrations_domain::class;
