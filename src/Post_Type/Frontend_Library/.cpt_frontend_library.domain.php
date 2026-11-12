<?php

namespace Org\Wplake\Advanced_Views\Post_Type\Frontend_Library;

use Architecture\Policy\Domain_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\layout_cpt_domain;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\post_selection_cpt_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\layout_library_adapter_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\selection_library_adapter_domain;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\frontend_library_domain;

class cpt_frontend_library_domain extends Domain_Policy {
	const WHITELIST_DOMAINS = [
		// todo break cycle-dependency (layouts <-> cpt_frontend_library)
		layout_cpt_domain::class,
		// todo break cycle-dependency (selections <-> cpt_frontend_library)
		post_selection_cpt_domain::class,
		frontend_library_domain::class,
		layout_library_adapter_domain::class,
		selection_library_adapter_domain::class,
	];
}
