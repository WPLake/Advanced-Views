<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use LogicException;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Widget_Dependencies;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

final class Layout_Elementor_Widget extends Widget_Base implements Widget_Dependencies {
	private static ?Cpt_Elementor_Widget $widget = null;

	/**
	 * Called once from Cpt_Widget_Registrar::register_widgets(), before Elementor ever renders.
	 */
	public static function set_dependencies( Cpt_Elementor_Widget $widget ): void {
		self::$widget = $widget;
	}

	public function get_name(): string {
		// important: do not use slash (/) in the widget name - it breaks Elementor's editor JS.
		return 'avf-layout';
	}

	public function get_title(): string {
		return __( 'AV Layout', 'acf-views' );
	}

	public function get_icon(): string {
		return 'eicon-post-list';
	}

	/**
	 * @return string[]
	 */
	public function get_categories(): array {
		// fixme.
		return array( Cpt_Item_Picker::CATEGORY );
	}

	/**
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'layout', 'advanced views' );
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'avf_layout_section',
			array(
				'label' => __( 'Layout', 'acf-views' ),
			)
		);

		$this->add_control(
			'layout_id',
			array(
				'label'   => __( 'Layout', 'acf-views' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => self::get_widget()->get_item_options(),
			)
		);
		Cpt_Elementor_Widget::add_action_links( $this );
		Layout_Object_Source::add_source_controls( $this );

		$this->end_controls_section();

		Cpt_Elementor_Widget::add_common_controls( $this );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$id_attrs     = array(
			'id'        => string( $settings, 'layout_id' ),
			'object-id' => Layout_Object_Source::resolve_object_id( $settings ),
		);
		$lookup_attrs = Layout_Object_Source::get_lookup_attributes( $settings );
		$common_attrs = Cpt_Elementor_Widget::build_attrs( $settings );

		$merged_attrs = array_merge(
			$id_attrs,
			$lookup_attrs,
			$common_attrs
		);

		$attrs = array_filter(
			$merged_attrs,
			fn( string $value ): bool => strlen( $value ) > 0
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::get_widget()->render( $attrs );
	}

	protected static function get_widget(): Cpt_Elementor_Widget {
		if ( ! self::$widget instanceof Cpt_Elementor_Widget ) {
			throw new LogicException( 'Cpt_Elementor_Widget dependencies were not set before rendering the widget.' );
		}

		return self::$widget;
	}
}
