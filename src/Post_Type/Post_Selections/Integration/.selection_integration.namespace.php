<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration;

use Architecture\Policy\Namespace_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Elementor\elementor_selection_namespace;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Gutenberg\gutenberg_selection_namespace;

class selection_integration_namespace extends Namespace_Policy {
	const DECOUPLED_CHILDREN = [
		elementor_selection_namespace::class,
		gutenberg_selection_namespace::class,
	];
}
