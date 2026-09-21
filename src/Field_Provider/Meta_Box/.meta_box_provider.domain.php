<?php

namespace Org\Wplake\Advanced_Views\Field_Provider\Meta_Box;

use Org\Wplake\Advanced_Views\Field_Provider\Core\field_provider_domain;

class meta_box_provider_domain extends field_provider_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
}
