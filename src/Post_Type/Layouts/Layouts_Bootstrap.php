<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layouts_Cpt as Layouts_Cpt_Hookable;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table\Layouts_Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Fields\Field_Markup;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;

class Layouts_Bootstrap extends Module_Bootstrap_Base {
	public function wire_instance_factories(): void {
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

		$instances = $this->get_instances();

		return array_merge( $resolved, $instances );
	}

	/**
	 * Generic (not layout-specific) hookables, created directly as the container can't host them per module
	 *
	 * @return Hookable[]
	 */
	protected function get_instances(): array {
		return array(
			$this->create_editor_settings(),
			$this->create_assets_reducer(),
		);
	}

	/**
	 * @return array<class-string, \Closure>
	 */
	protected function get_wire_resolves(): array {
		return array(
			Layout_Factory::class       => fn(): Layout_Factory => $this->make_factory(),
			Layouts_Cpt_Hookable::class => fn(): Layouts_Cpt_Hookable => $this->make_cpt_hookable(),
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

	protected function make_cpt_hookable(): Layouts_Cpt_Hookable {
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );

		return new Layouts_Cpt_Hookable( $layouts_cpt, $settings_storage );
	}

	protected function create_editor_settings(): Cpt_Gutenberg_Editor_Settings {
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$cpt_name    = $layouts_cpt->cpt_name();

		return new Cpt_Gutenberg_Editor_Settings( $cpt_name );
	}

	protected function create_assets_reducer(): Cpt_Assets_Reducer {
		$settings_storage = $this->resolve( Settings_Storage::class );
		$plugin           = $this->resolve( Plugin::class );
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );
		$cpt_name         = $layouts_cpt->cpt_name();

		return new Cpt_Assets_Reducer( $settings_storage, $plugin, $cpt_name );
	}

	protected function make_factory(): Layout_Factory {
		$front_assets     = $this->resolve( Front_Assets::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$markup           = $this->resolve( Layout_Markup::class );
		$renderer_storage = $this->resolve( Template_Renderer_Storage::class );
		$field_markup     = $this->resolve( Field_Markup::class );
		$provider_cluster = $this->resolve( Field_Provider_Cluster::class );

		return new Layout_Factory(
			$front_assets,
			PHP_Template_Engine::NAME,
			$settings_storage,
			$markup,
			$renderer_storage,
			$field_markup,
			$provider_cluster
		);
	}
}
