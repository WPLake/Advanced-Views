<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Integrations\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use LogicException;
use Org\Wplake\Advanced_Views\Post_Type\Core\Integrations\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Core\Integrations\Elementor\Cpt_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Core\Integrations\Elementor\Widget_Dependencies;
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

		self::get_widget()->add_action_links( $this );

		$this->register_source_controls();

		$this->end_controls_section();

		Cpt_Elementor_Widget::add_common_controls( $this );
	}

	protected function register_source_controls(): void {
		$this->add_control(
			'object_source',
			array(
				'label'   => __( 'Object Source', 'acf-views' ),
				'type'    => Controls_Manager::SELECT2,
				'default' => 'post',
				'options' => array(
					'post'    => __( 'Post', 'acf-views' ),
					'options' => __( 'Custom options page (ACF/MB)', 'acf-views' ),
					'user'    => __( 'User', 'acf-views' ),
					'term'    => __( 'Term', 'acf-views' ),
					'menu'    => __( 'Menu', 'acf-views' ),
					'comment' => __( 'Comment', 'acf-views' ),
				),
			)
		);

		$this->add_control(
			'post_lookup',
			array(
				'label'     => __( 'Look Up Post By', 'acf-views' ),
				'type'      => Controls_Manager::SELECT2,
				'default'   => 'current',
				'options'   => array(
					'current' => __( 'Current Post', 'acf-views' ),
					'id'      => __( 'Post ID', 'acf-views' ),
					'slug'    => __( 'Post Slug', 'acf-views' ),
				),
				'condition' => array( 'object_source' => 'post' ),
			)
		);
		$this->add_control(
			'post_id',
			array(
				'label'       => __( 'Post ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => self::with_dynamic_value_help(),
				'condition'   => array(
					'object_source' => 'post',
					'post_lookup'   => 'id',
				),
			)
		);
		$this->add_control(
			'post_slug',
			array(
				'label'       => __( 'Post Slug', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => self::with_dynamic_value_help(),
				'condition'   => array(
					'object_source' => 'post',
					'post_lookup'   => 'slug',
				),
			)
		);
		$this->add_control(
			'user_id',
			array(
				'label'       => __( 'User ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => self::with_dynamic_value_help( __( 'Leave empty to use the current user.', 'acf-views' ) ),
				'condition'   => array( 'object_source' => 'user' ),
			)
		);
		$this->add_control(
			'term_id',
			array(
				'label'       => __( 'Term ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => self::with_dynamic_value_help(
					__( 'Leave empty to use the current term on a term page.', 'acf-views' )
				),
				'condition'   => array( 'object_source' => 'term' ),
			)
		);
		$this->add_control(
			'menu_slug',
			array(
				'label'       => __( 'Menu Slug', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => self::with_dynamic_value_help(),
				'condition'   => array( 'object_source' => 'menu' ),
			)
		);
		$this->add_control(
			'comment_id',
			array(
				'label'       => __( 'Comment ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => self::with_dynamic_value_help(),
				'condition'   => array( 'object_source' => 'comment' ),
			)
		);
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$attrs = array_filter(
			array_merge(
				array(
					'id'        => string( $settings, 'layout_id' ),
					'object-id' => Layout_Object_Source::resolve_object_id( $settings ),
				),
				Layout_Object_Source::get_lookup_attributes( $settings ),
				Cpt_Elementor_Widget::build_attrs( $settings )
			),
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

	/**
	 * Every lookup control below shares this same hint (mirroring sourceFieldBuilders.ts's dynamicValueHelp), so
	 * it's kept as one reusable, single translated string instead of being retyped (and re-translated) per field.
	 */
	protected static function with_dynamic_value_help( string $description = '' ): string {
		$help = __( 'To pull it from another field, add that field inside the current Layout instead.', 'acf-views' );

		return '' !== $description ? $description . ' ' . $help : $help;
	}
}
