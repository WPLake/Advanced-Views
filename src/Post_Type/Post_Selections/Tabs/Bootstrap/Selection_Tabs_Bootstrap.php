<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Tabs\Selection_Git_Tabs;

class Selection_Tabs_Bootstrap extends Module_Bootstrap_Base {
	public function wire_instances(): void {
		$wire_resolves = $this->get_wire_resolves();

		foreach ( $wire_resolves as $class_name => $factory ) {
			$this->wire( $class_name, $factory );
		}
	}

	public function get_hookables( Route_Detector $route_detector ): array {
		$hookable_classes = $this->get_hookable_classes();
		$resolved         = array_map(
			fn( string $class_name ): Hookable => $this->resolve( $class_name ),
			$hookable_classes
		);

		$fs_only_tab = $this->resolve( Selection_Tabs_Factory::class )->create_fs_only_tab();

		return array_merge( $resolved, array( $fs_only_tab ) );
	}

	/**
	 * @return array<class-string<Hookable>>
	 */
	protected function get_hookable_classes(): array {
		return array(
			Post_Selections_Bulk_Validation_Tab::class,
			Post_Selections_Pre_Built_Tab::class,
			Selection_Git_Tabs::class,
		);
	}

	/**
	 * @return array<class-string, \Closure>
	 */
	protected function get_wire_resolves(): array {
		return array(
			Post_Selections_Pre_Built_Tab::class       => fn(): Post_Selections_Pre_Built_Tab => $this->resolve( Selection_Tabs_Factory::class )->create_pre_built_tab(),
			Post_Selections_Bulk_Validation_Tab::class => fn(): Post_Selections_Bulk_Validation_Tab => $this->resolve( Selection_Tabs_Factory::class )->create_bulk_validation_tab(),
		);
	}
}
