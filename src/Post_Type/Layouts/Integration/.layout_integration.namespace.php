<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration;

use Architecture\Policy\Namespace_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\elementor_layout_namespace;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\gutenberg_layout_namespace;

class layout_integration_namespace extends Namespace_Policy {
	const DECOUPLED_CHILDREN = [
		elementor_layout_namespace::class,
		gutenberg_layout_namespace::class,
	];
}
