<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt_Base\Integrations\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * The contract every concrete Layout/Selection Elementor widget implements so Cpt_Widget_Registrar can hand
 * it its per-CPT Cpt_Elementor_Widget instance right before registering it - Elementor re-constructs the widget
 * with no args per render, so this is the only way the widget ever receives it.
 */
interface Widget_Dependencies {
	public static function set_dependencies( Cpt_Elementor_Widget $widget ): void;
}
