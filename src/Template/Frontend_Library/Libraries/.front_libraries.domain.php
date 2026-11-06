<?php

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\field_type_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\frontend_library_domain;

class front_libraries_domain extends frontend_library_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		field_type_domain::class,
		...parent::WHITELIST_DOMAINS,
	];
}
