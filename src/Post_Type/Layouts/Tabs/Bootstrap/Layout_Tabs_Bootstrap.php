<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layout_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Tabs\Layouts_Pre_Built_Tab;

class Layout_Tabs_Bootstrap extends Module_Bootstrap_Base {
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

		$fs_only_tab = $this->resolve( Layout_Tabs_Factory::class )->create_fs_only_tab();

		return array_merge( $resolved, array( $fs_only_tab ) );
	}

	/**
	 * @return array<class-string<Hookable>>
	 */
	protected function get_hookable_classes(): array {
		return array(
			Layouts_Bulk_Validation_Tab::class,
			Layouts_Pre_Built_Tab::class,
			Layout_Git_Tabs::class,
		);
	}

	/**
	 * @return array<class-string, \Closure>
	 */
	protected function get_wire_resolves(): array {
		return array(
			Layouts_Pre_Built_Tab::class       => fn(): Layouts_Pre_Built_Tab => $this->resolve( Layout_Tabs_Factory::class )->create_pre_built_tab(),
			Layouts_Bulk_Validation_Tab::class => fn(): Layouts_Bulk_Validation_Tab => $this->resolve( Layout_Tabs_Factory::class )->create_bulk_validation_tab(),
		);
	}
}
