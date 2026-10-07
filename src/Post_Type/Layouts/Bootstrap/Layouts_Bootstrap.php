<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layouts_Cpt as Layouts_Cpt_Hookable;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;

class Layouts_Bootstrap extends Module_Bootstrap_Base {
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

		$factory    = $this->resolve( Layouts_Factory::class );
		$editor_settings = $factory->create_editor_settings();
		$assets_reducer  = $factory->create_assets_reducer();

		// Generic (not layout-specific) hookables, created directly as the container can't host them per module.
		$instances = array( $editor_settings, $assets_reducer );

		return array_merge( $resolved, $instances );
	}

	/**
	 * @return array<class-string, \Closure>
	 */
	protected function get_wire_resolves(): array {
		return array(
			Shortcode_Gutenberg_Block::class => fn(): Shortcode_Gutenberg_Block => $this->resolve( Layouts_Factory::class )->create_shortcode_block(),
		);
	}

	/**
	 * @return array<class-string<Hookable>>
	 */
	protected function get_hookable_classes(): array {
		return array(
			Layout_Meta_Boxes::class,
			Layouts_Cpt_Hookable::class,
			Layouts_Cpt_Table::class,
			Layout_Save_Actions::class,
			Layout_Shortcode::class,
			Layout_Git_Box::class,
			Layout_Interactive_Fields::class,
		);
	}
}
