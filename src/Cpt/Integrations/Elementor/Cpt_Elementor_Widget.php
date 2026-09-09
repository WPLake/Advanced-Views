<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt\Integrations\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Elements_Manager;
use Elementor\Widget_Base;
use LogicException;
use Org\Wplake\Advanced_Views\Cpt\Integrations\Cpt_Integration_Block;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * Stateless logic shared by every Elementor widget backed by a Cpt_Settings_Storage (Layout, Post Selection...) -
 * the Elementor-specific counterpart to Cpt_Gutenberg_Block, both sitting on top of Cpt_Integration_Block.
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

	private function __construct() {
	}

	public static function add_category( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			Cpt_Integration_Block::CATEGORY,
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
	public static function add_action_links_control( Widget_Base $widget, Cpt_Elementor_Bridge $bridge ): void {
		$widget->add_control(
			self::ACTION_LINKS_CONTROL_ID,
			array(
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => self::get_action_links_html( $bridge ),
			)
		);
	}

	public static function get_common_controls( Widget_Base $widget ): void {
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
	public static function get_item_options( Cpt_Elementor_Bridge $bridge ): array {
		$options = array();

		foreach ( $bridge->get_items_list() as $unique_id => $item ) {
			$options[ $unique_id ] = $item['title'];
		}

		return $options;
	}

	public static function require_bridge( ?Cpt_Elementor_Bridge $bridge ): Cpt_Elementor_Bridge {
		if ( ! $bridge instanceof Cpt_Elementor_Bridge ) {
			throw new LogicException( 'Cpt_Elementor_Bridge dependencies were not set before rendering the widget.' );
		}

		return $bridge;
	}

	protected static function get_action_links_html( Cpt_Elementor_Bridge $bridge ): string {
		$add_new_label = sprintf(
		// translators: %s is a singular post-type name, e.g. "Layout".
			__( 'Add new %s', 'acf-views' ),
			$bridge->get_item_label()
		);

		return sprintf(
			'<a href="%s" target="_blank">%s</a> - <a href="#" class="avf-action-links__refresh">%s</a>',
			esc_url( $bridge->get_new_item_url() ),
			esc_html( $add_new_label ),
			esc_html__( 'Refresh', 'acf-views' )
		);
	}
}
