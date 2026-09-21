<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post\Post_Type\Core\Integrations\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Elements_Manager;
use Elementor\Plugin as Elementor_Plugin;
use Elementor\Widget_Base;
use Org\Wplake\Advanced_Views\Post\Post_Type\Core\Integrations\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post\Post_Type\Core\Integrations\Cpt_Renderer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * Logic shared by every Elementor widget backed by a Cpt_Settings_Storage (Layout, Post Selection...) - the
 * Elementor-specific counterpart to Cpt_Gutenberg_Block. One instance per CPT, built by Cpt_Widget_Registrar
 * and handed to the concrete widget class via its own set_dependencies() - the same role Cpt_Elementor_Bridge
 * used to play, folded in here since there's no remaining state Elementor's per-render widget re-construction
 * would otherwise lose.
 */
final class Cpt_Elementor_Widget {
	// the RAW_HTML control itemActionLinks.ts locates via `[data-setting]` to keep in sync with the selected item.
	const ACTION_LINKS_CONTROL_ID = 'avf_action_links';

	/**
	 * Elementor control id => shortcode attribute name.
	 */
	const COMMON_CONTROLS = array(
		'class'              => 'class',
		'user_with_roles'    => 'user-with-roles',
		'user_without_roles' => 'user-without-roles',
		'custom_arguments'   => 'custom-arguments',
	);

	private Cpt_Item_Picker $item_picker;
	private Cpt_Renderer $renderer;

	public function __construct( Cpt_Item_Picker $item_picker, Cpt_Renderer $renderer ) {
		$this->item_picker = $item_picker;
		$this->renderer    = $renderer;
	}

	public static function add_category( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			Cpt_Item_Picker::CATEGORY,
			array(
				'title' => __( 'Advanced Views', 'acf-views' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Adds the "Add new / Refresh" links row right after the item-picker SELECT2 control - the Elementor
	 * counterpart to Gutenberg's actionLinksElement (see itemPicker.ts). Elementor registers a widget's controls
	 * once per widget type, not per saved instance, so only the type-level state (nothing selected yet) can be
	 * rendered here; itemActionLinks.ts swaps in the "Edit" link once it knows which item this particular
	 * instance has selected.
	 */
	public function add_action_links( Widget_Base $widget ): void {
		$widget->add_control(
			self::ACTION_LINKS_CONTROL_ID,
			array(
				'type' => Controls_Manager::RAW_HTML,
				// HTML is set on the JS side.
				'raw'  => '',
			)
		);
	}

	public static function add_common_controls( Widget_Base $widget ): void {
		$widget->start_controls_section(
			'avf_advanced_section',
			array(
				'label' => __( 'Advanced Views', 'acf-views' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$widget->add_control(
			'class',
			array(
				'label' => __( 'Additional CSS Class', 'acf-views' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$widget->add_control(
			'user_with_roles',
			array(
				'label'       => __( 'Show for User Roles', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Comma-separated list of roles. Leave empty to show for everyone.', 'acf-views' ),
			)
		);
		$widget->add_control(
			'user_without_roles',
			array(
				'label'       => __( 'Hide for User Roles', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Comma-separated list of roles.', 'acf-views' ),
			)
		);
		$widget->add_control(
			'custom_arguments',
			array(
				'label' => __( 'Custom Arguments', 'acf-views' ),
				'type'  => Controls_Manager::TEXTAREA,
			)
		);

		$widget->end_controls_section();
	}

	/**
	 * @param array<string,mixed> $settings
	 *
	 * @return array<string,string>
	 */
	public static function build_attrs( array $settings ): array {
		$attrs = array();

		foreach ( self::COMMON_CONTROLS as $control_id => $attr_name ) {
			$attrs[ $attr_name ] = string( $settings, $control_id );
		}

		return $attrs;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_item_options(): array {
		return $this->item_picker->get_flat_items();
	}

	/**
	 * The single entry point Layout_Elementor_Widget/Selection_Elementor_Widget::render() calls on their held
	 * instance: dispatches to Cpt_Renderer::render()/render_preview() the same way
	 * Layout_Gutenberg_Block/Selection_Gutenberg_Block::render_block() does, only gated by is_editor_preview()
	 * instead of Route_Detector::is_admin_route().
	 *
	 * @param array<string,string> $attrs
	 */
	public function render( array $attrs ): string {
		return self::is_editor_preview() ?
			$this->renderer->render_preview( $attrs, true ) :
			$this->renderer->render( $attrs );
	}

	/**
	 * Elementor's main canvas preview loads the real front-end URL through normal template routing (caught by
	 * is_preview_mode()'s 'elementor-preview' query arg), while its per-widget AJAX partial re-render runs through
	 * admin-ajax.php with edit mode explicitly turned on for the duration of the render (caught by is_edit_mode()) -
	 * neither is covered by Route_Detector::is_admin_route().
	 */
	protected static function is_editor_preview(): bool {
		$elementor = Elementor_Plugin::$instance;

		return $elementor->editor->is_edit_mode() ||
				$elementor->preview->is_preview_mode();
	}
}
