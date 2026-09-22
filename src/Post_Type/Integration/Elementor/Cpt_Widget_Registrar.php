<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Elements_Manager;
use Elementor\Widget_Base;
use Elementor\Widgets_Manager;
use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Renderer;

/**
 * Registers the Elementor category and widget(s) for one CPT (Layout, Post Selection...) - one instance per CPT.
 * Editor/preview script enqueueing is a separate standalone actor (Layout_Elementor_Assets,
 * Selection_Elementor_Assets - registered alongside this class, not through it), since asset paths/localized var
 * names are the one piece of config that genuinely differs per CPT and doesn't belong hard-coded into this
 * otherwise fully generic registrar.
 */
final class Cpt_Widget_Registrar extends Hookable implements Hooks_Interface {
	private Cpt_Item_Picker $item_picker;
	private Cpt_Renderer $renderer;

	/**
	 * @var class-string<Widget_Base&Widget_Dependencies>[]
	 */
	private array $widgets;

	public function __construct( Cpt_Item_Picker $item_picker, Cpt_Renderer $renderer ) {
		$this->item_picker = $item_picker;
		$this->renderer    = $renderer;
		$this->widgets     = array();
	}

	/**
	 * @param class-string<Widget_Base&Widget_Dependencies> $widget
	 */
	public function add_widget( string $widget ): void {
		$this->widgets[] = $widget;
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		self::add_action(
			'elementor/elements/categories_registered',
			fn( Elements_Manager $elements_manager ) => Cpt_Elementor_Widget::add_category( $elements_manager )
		);
		self::add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	public function register_widgets( Widgets_Manager $widgets_manager ): void {
		$widget = new Cpt_Elementor_Widget( $this->item_picker, $this->renderer );

		foreach ( $this->widgets as $widget_class ) {
			$widget_class::set_dependencies( $widget );

			$widgets_manager->register( new $widget_class() );
		}
	}
}
