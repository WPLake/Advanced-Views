<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Types;

use Org\Wplake\Advanced_Views\Post_Type\Core\post_type_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\layouts_namespace;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selections\post_selections_namespace;

class post_types_domain extends post_type_domain {
	const WHITELIST_DOMAINS = [
		parent::class,
		...parent::WHITELIST_DOMAINS,
	];
	const DECOUPLED_CHILDREN = [
		layouts_namespace::class,
		post_selections_namespace::class,
	];
	const DECOUPLE_EXCEPTIONS = [
		post_selections_namespace::class => [ layouts_namespace::class ],
	];
}

return post_types_domain::class;
