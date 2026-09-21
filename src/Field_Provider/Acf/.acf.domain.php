<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Acf;

use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;

class acf_domain extends field_provider_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
