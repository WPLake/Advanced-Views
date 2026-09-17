<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers;

use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\acf_namespace;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\meta_box_namespace;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods\pods_namespace;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\woo_namespace;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\wp_namespace;

class field_providers_domain extends field_provider_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
	const DECOUPLED_CHILDREN = [
		acf_namespace::class,
		meta_box_namespace::class,
		pods_namespace::class,
		woo_namespace::class,
		wp_namespace::class,
	];
}

return field_providers_domain::class;
