<?php

namespace Org\Wplake\Advanced_Views\Template\Library_Concept\Libraries;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\field_type_domain;
use Org\Wplake\Advanced_Views\Template\Library_Concept\Core\library_concept_domain;

class front_libraries_domain extends library_concept_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		field_type_domain::class,
		...parent::WHITELIST_DOMAINS,
	];
}
