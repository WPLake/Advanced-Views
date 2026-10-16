<?php

namespace Org\Wplake\Advanced_Views\Field_Value\WordPress;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Field_Value\Core\field_value_domain;

class wordpress_field_value_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		field_value_domain::class,
	];
}
