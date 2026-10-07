<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Pre_Built_Tab;

class Layout_Tabs_Bootstrap extends Module_Bootstrap_Base {
	public function get_instance_factories(): array {
		return array(
			Layouts_Pre_Built_Tab::class       => fn() =>
				$this->resolve( Layout_Tabs_Factory::class )
					->pre_built_tab(),
			Layouts_Bulk_Validation_Tab::class => fn() =>
				$this->resolve( Layout_Tabs_Factory::class )
					->bulk_validation_tab(),
		);
	}

	public function get_hookable_classes(): array {
		return array(
			Layouts_Bulk_Validation_Tab::class,
			Layouts_Pre_Built_Tab::class,
			Layout_Git_Tabs::class,
		);
	}

	public function get_hookable_factories(): array {
		$factory = $this->resolve( Layout_Tabs_Factory::class );

		return array( Fs_Only_Tab::class => fn() => $factory->fs_only_tab() );
	}
}
