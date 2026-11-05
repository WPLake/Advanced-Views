<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Menu;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Meta\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Meta\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Html\Composite_Attribute;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;
use WP_Post;
use function Org\Wplake\Advanced_Views\Template\Html\compose_attribute;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Menu_Items_Field extends Markup_Field_Base {
	const IS_ACTIVE       = 'isActive';
	const IS_CHILD_ACTIVE = 'isChildActive';
	const CHILDREN        = 'children';

	use Custom_Field;

	private Link_Field $link_field;

	public function __construct( Link_Field $link_field ) {
		$this->link_field = $link_field;
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_internal_item_layout( string $item_id, Template_Field_Meta $markup_field_data ): array {
		return $this->link_field->compose_html( $item_id, $markup_field_data );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_external_item_layout( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$id_var        = $token_factory->variable( $field_id )
										->add_item_path( self::LAYOUT_ID );
		$object_id_var = $token_factory->variable( $item_id )
										->add_item_path( self::VALUE );

		$shortcode = $token_factory->shortcode( Hard_Layout_Cpt::cpt_name() )
			->set_attributes(
				array(
					'id'        => $id_var,
					'object-id' => $object_id_var,
				)
			);

		return array( $shortcode );
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$value_var = $token_factory->variable( $field_id )
			->add_item_path( self::VALUE );
		$item_var  = $token_factory->variable( 'menu_item' );

		$loop_body = $this->compose_loop_body( $field_id, $item_var->get_name(), $markup_field_data );

		$loop = $token_factory->loop()
						->set_source_variable( $value_var )
						->set_item_variable( $item_var )
						->set_body( $loop_body );

		return array( $loop );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_loop_body( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$is_active_var        = $token_factory->variable( $item_id )
												->add_item_path( self::IS_ACTIVE );
		$is_child_active_var  = $token_factory->variable( $item_id )
												->add_item_path( self::IS_CHILD_ACTIVE );
		$is_active_comparison = $token_factory->comparison()
												->set_left_operand( $is_active_var )
												->set_comparison_or()
												->set_right_operand( $is_child_active_var );
		$children_branch      = $this->children_branch( $field_id, $item_id, $markup_field_data );
		$children_if          = $token_factory->if()->set_if_branch( $children_branch );

		$item_class = $this->compose_class( 'menu-item', 'menu-item--active', $is_active_comparison, $markup_field_data );
		$item_nodes = $this->compose_item_markup( $field_id, $item_id, $markup_field_data );

		$item = tag( 'li' )
			->attributes( array( 'class' => $item_class ) )
			->content( ...array_merge( $item_nodes, array( $children_if ) ) );

		return array( $item );
	}

	protected function children_branch( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): IF_Branch {
		$token_factory = $markup_field_data->get_token_factory();

		$children_var = $token_factory->variable( $item_id )
			->add_item_path( self::CHILDREN );

		$body = $this->compose_children( $field_id, $item_id, $markup_field_data );

		return $token_factory->if_branch()
			->set_condition( $children_var )
			->set_body( $body );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_children( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$field_context = $markup_field_data->get_context();

		$token_factory = $markup_field_data->get_token_factory();

		$item_class = $this->get_item_class(
			'sub-menu',
			$field_context->get_layout_settings(),
			$field_context->get_field_settings()
		);

		$children_var      = $token_factory->variable( $item_id )
											->add_item_path( self::CHILDREN );
		$sub_menu_item_var = $token_factory->variable( 'sub_menu_item' );

		$loop_body = $this->compose_sub_loop_body( $field_id, $sub_menu_item_var->get_name(), $markup_field_data );

		$loop = $token_factory->loop()
						->set_source_variable( $children_var )
						->set_item_variable( $sub_menu_item_var )
						->set_body( $loop_body );

		$tags = tag( 'ul' )
			->attributes( array( 'class' => $item_class ) )
			->content( $loop );

		return array( $tags );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_sub_loop_body( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$is_active_var = $token_factory->variable( $item_id )
										->add_item_path( self::IS_ACTIVE );

		$item_class = $this->compose_class( 'sub-menu-item', 'sub-menu-item--active', $is_active_var, $markup_field_data );
		$item_nodes = $this->compose_item_markup( $field_id, $item_id, $markup_field_data );

		$item = tag( 'li' )
			->attributes( array( 'class' => $item_class ) )
			->content( ...$item_nodes );

		return array( $item );
	}

	protected function compose_class(
		string $base_suffix,
		string $conditional_suffix,
		Statement_Token $condition,
		Template_Field_Meta $markup_field_data
	): Composite_Attribute {
		$field_context = $markup_field_data->get_context();

		$token_factory = $markup_field_data->get_token_factory();
		$layout_data   = $field_context->get_layout_settings();
		$field_data    = $field_context->get_field_settings();

		$base_class        = $this->get_item_class( $base_suffix, $layout_data, $field_data );
		$conditional_class = $this->get_item_class( $conditional_suffix, $layout_data, $field_data );

		$active_class = $token_factory->conditional_value()
										->set_comparison( $condition )
										->set_accept_value( $token_factory->literal( sprintf( ' %s', $conditional_class ) ) )
										->set_decline_value( $token_factory->literal( '' ) );

		return compose_attribute( array( $base_class, $active_class ) );
	}

	protected function is_active_item( WP_Post $wp_post ): bool {
		$posts_page_id = get_option( 'page_for_posts' );
		$posts_page_id = is_numeric( $posts_page_id ) ?
			(int) $posts_page_id :
			0;

		$object_id = int( $wp_post, 'object_id' );

		// active if the current menu is for current page, or
		// the current menu for blog and the current page is post or
		// the current menu for blog and the current page is author page
		// the current menu for blog and the current page is category page.

		if ( ( 0 !== $object_id && get_queried_object_id() === $object_id ) ||
			( $object_id === $posts_page_id && is_singular( 'post' ) ) ||
			( $object_id === $posts_page_id && is_author() ) ||
			( $object_id === $posts_page_id && is_category() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param WP_Post[] $children
	 *
	 * @return array<string,mixed>
	 */
	protected function get_item_twig_args(
		?WP_Post $wp_post,
		array $children,
		Raw_Field_Value $field_value,
		bool $is_for_validation = false
	): array {
		if ( $field_value->get_context()->get_field_settings()->has_external_layout() ) {
			$args = array(
				self::VALUE           => 0,
				self::IS_ACTIVE       => false,
				self::IS_CHILD_ACTIVE => false,
				self::CHILDREN        => array(),
			);

			if ( $is_for_validation ) {
				return $args;
			}

			$is_child_active     = false;
			$args[ self::VALUE ] = null !== $wp_post ?
				$wp_post->ID :
				0;

			foreach ( $children as $child ) {
				$is_sub_active = $this->is_active_item( $child );

				$args[ self::CHILDREN ][] = array(
					self::VALUE     => $child->ID,
					self::IS_ACTIVE => $is_sub_active,
				);

				$is_child_active = $is_child_active || $is_sub_active;
			}

			return array_merge(
				$args,
				array(
					self::IS_ACTIVE       => null !== $wp_post && $this->is_active_item( $wp_post ),
					self::IS_CHILD_ACTIVE => $is_child_active,
				)
			);
		}

		$link_args = null !== $wp_post ?
			$this->get_menu_item_info( $wp_post ) :
			array();

		$field_value->set_value( $link_args );

		$args = ! $is_for_validation ?
			$this->link_field->get_template_variables( $field_value ) :
			$this->link_field->get_validation_template_variables( $field_value );

		$args = array_merge(
			$args,
			array(
				self::IS_ACTIVE       => false,
				self::IS_CHILD_ACTIVE => false,
				self::CHILDREN        => array(),
			)
		);

		if ( $is_for_validation ) {
			$child_args = $this->link_field->get_validation_template_variables( $field_value );

			$args[ self::CHILDREN ][] = array_merge(
				$child_args,
				array(
					self::IS_ACTIVE => false,
				)
			);

			return $args;
		}

		$is_child_active = false;

		foreach ( $children as $child_menu_item ) {
			$link_args = $this->get_menu_item_info( $child_menu_item );

			$field_value->set_value( $link_args );

			$child_args = $this->link_field->get_template_variables( $field_value );

			$is_sub_active = $this->is_active_item( $child_menu_item );

			$args[ self::CHILDREN ][] = array_merge(
				$child_args,
				array(
					self::IS_ACTIVE => $is_sub_active,
				)
			);

			$is_child_active = $is_child_active || $is_sub_active;
		}

		return array_merge(
			$args,
			array(
				self::IS_ACTIVE       => null !== $wp_post && $this->is_active_item( $wp_post ),
				self::IS_CHILD_ACTIVE => $is_child_active,
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$layout_id = $field_value->get_context()->get_field_settings()->get_short_unique_acf_view_id();

		$args = array(
			self::VALUE     => array(),
			self::VIEW_ID   => $layout_id,
			self::LAYOUT_ID => $layout_id,
		);

		$menu = $this->get_term( $field_value->get_value(), 'nav_menu' );

		if ( null === $menu ) {
			return $args;
		}

		$menu_items = wp_get_nav_menu_items( $menu->term_id );
		$menu_items = false === $menu_items ?
			array() :
			$menu_items;

		$children = array();
		foreach ( $menu_items as $menu_item ) {
			if ( ! $menu_item->menu_item_parent ) {
				continue;
			}

			$children[ $menu_item->menu_item_parent ][] = $menu_item;
		}

		foreach ( $menu_items as $menu_item ) {
			// top level only.
			if ( $menu_item->menu_item_parent ) {
				continue;
			}

			$args[ self::VALUE ][] = $this->get_item_twig_args(
				$menu_item,
				$children[ $menu_item->ID ] ?? array(),
				$field_value
			);
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$item_args = $this->get_item_twig_args(
			null,
			array(),
			$field_value,
			true
		);

		return array(
			self::VALUE => array(
				$item_args,
			),
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return true;
	}

	public function get_custom_field_wrapper_tag(): string {
		return 'ul';
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		return array_merge(
			parent::get_conditional_fields( $field_meta ),
			array(
				Field_Settings::FIELD_IS_LINK_TARGET_BLANK,
				Field_Settings::FIELD_ACF_VIEW_ID,
			)
		);
	}
}
