<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Selection_Git_Tabs;

class Selection_Tabs_Bootstrap extends Module_Bootstrap_Base {
	public function get_instance_factories(): array {
		return array(
			Post_Selections_Pre_Built_Tab::class       => fn() =>
				$this->resolve( Selection_Tabs_Factory::class )
					->pre_built_tab(),
			Post_Selections_Bulk_Validation_Tab::class => fn() =>
				$this->resolve( Selection_Tabs_Factory::class )
					->bulk_validation_tab(),
		);
	}

	protected function get_hookable_classes(): array {
		return array(
			Post_Selections_Bulk_Validation_Tab::class,
			Post_Selections_Pre_Built_Tab::class,
			Selection_Git_Tabs::class,
		);
	}

	protected function resolve_hookable_instances( Route_Detector $route_detector ): array {
		$factory     = $this->resolve( Selection_Tabs_Factory::class );
		$fs_only_tab = $factory->fs_only_tab();

		return array( $fs_only_tab );
	}
}
