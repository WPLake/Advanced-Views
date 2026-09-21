<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Woo;

use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;

class woo_provider_domain extends field_provider_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
