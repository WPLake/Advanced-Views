<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Postbox_Printer;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_Post;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Layout_Meta_Boxes extends Cpt_Meta_Boxes {
	private Field_Provider_Cluster $provider_cluster;
	private Layout_Settings_Storage $layouts_settings_storage;
	private Layout_Cpt $layouts_cpt;

	public function __construct(
		Postbox_Printer $postbox,
		Plugin $plugin,
		Layout_Settings_Storage $layouts_settings_storage,
		Field_Provider_Cluster $provider_cluster,
		Layout_Cpt $public_cpt,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $postbox, $plugin, $html_renderer );

		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->provider_cluster         = $provider_cluster;
		$this->layouts_cpt              = $public_cpt;
	}

	public function print_related_groups_meta_box(
		Layout_Settings $layout_settings,
		bool $is_skip_not_found_message = false
	): void {
		$nodes = $this->related_groups_nodes( $layout_settings, $is_skip_not_found_message );

		$this->html_renderer->print_html( ...$nodes );
	}

	public function print_related_views_meta_box(
		Layout_Settings $layout_settings,
		bool $is_skip_not_found_message = false
	): void {
		$nodes = $this->related_views_nodes( $layout_settings, $is_skip_not_found_message );

		$this->html_renderer->print_html( ...$nodes );
	}

	/**
	 * @return Html_Node[]
	 */
	public function related_groups_nodes(
		Layout_Settings $layout_settings,
		bool $is_skip_not_found_message = false
	): array {
		$used_meta_group_ids = $layout_settings->get_used_meta_group_ids();
		$groups_count        = count( $used_meta_group_ids );

		if ( 0 === $groups_count ) {
			$message = __( 'No assigned ACF Groups.', 'acf-views' );

			return $is_skip_not_found_message ?
				array() :
				array( $message );
		}

		$nodes            = array();
		$group_last_index = $groups_count - 1;
		$counter          = - 1;

		foreach ( $used_meta_group_ids as $group_id ) {
			++$counter;

			$group_link = $this->provider_cluster->get_group_link_by_group_id( $group_id );

			if ( is_array( $group_link ) ) {
				$is_last    = $counter === $group_last_index;
				$link_nodes = self::compose_link_nodes( $group_link['url'], $group_link['title'], $is_last );
				$nodes      = array_merge( $nodes, $link_nodes );
			}
		}

		return $nodes;
	}

	/**
	 * @return Html_Node[]
	 */
	public function related_views_nodes(
		Layout_Settings $layout_settings,
		bool $is_skip_not_found_message = false
	): array {
		$related_view_unique_ids = $this->get_related_view_unique_ids( $layout_settings );
		$views_count             = count( $related_view_unique_ids );

		if ( 0 === $views_count ) {
			$plural_name      = $this->layouts_cpt
				->labels()
				->plural_name();
			// translators: %s is the plural name of the CPT.
			$message_template = __( 'No assigned %s.', 'acf-views' );
			$message          = sprintf( $message_template, $plural_name );

			return $is_skip_not_found_message ?
				array() :
				array( $message );
		}

		$nodes           = array();
		$last_item_index = $views_count - 1;
		$counter         = 0;

		foreach ( $related_view_unique_ids as $related_view_unique_id ) {
			$layout_settings = $this->layouts_settings_storage->get( $related_view_unique_id );
			$edit_link       = $layout_settings->get_edit_post_link();
			$is_last         = $counter === $last_item_index;
			$link_nodes      = self::compose_link_nodes( $edit_link, $layout_settings->title, $is_last );
			$nodes           = array_merge( $nodes, $link_nodes );

			++$counter;
		}

		return $nodes;
	}

	public function add_meta_boxes(): void {
		$cpt_name        = $this->get_cpt_name();
		$cpt_names       = array( $cpt_name );
		$plural_name     = $this->layouts_cpt
			->labels()
			->plural_name();
		// translators: %s is the plural name of the CPT.
		$nested_template = __( 'Nested %s', 'acf-views' );
		$nested_title    = sprintf( $nested_template, $plural_name );

		add_meta_box(
			'acf-views_shortcode',
			__( 'Embed the Layout', 'acf-views' ),
			fn( $post ) => $this->print_embed_meta_box( $post ),
			$cpt_names,
			'side',
			'high'
		);

		add_meta_box(
			'acf-views_related_groups',
			__( 'Field Groups', 'acf-views' ),
			fn( WP_Post $wp_post ) => $this->print_groups_meta_box( $wp_post ),
			$cpt_names,
			'side',
			'core'
		);

		add_meta_box(
			'acf-views_related_views',
			$nested_title,
			fn( WP_Post $wp_post ) => $this->print_views_meta_box( $wp_post ),
			$cpt_names,
			'side',
			'core'
		);

		parent::add_meta_boxes();
	}

	protected function get_cpt_name(): string {
		return Hard_Layout_Cpt::cpt_name();
	}

	/**
	 * @return string[]
	 */
	protected function get_related_view_unique_ids( Layout_Settings $layout_settings ): array {
		$related_view_ids = array();

		foreach ( $layout_settings->items as $item ) {
			$view_id   = $item->field->acf_view_id;
			$is_filled = strlen( $view_id ) > 0;

			if ( $is_filled ) {
				$related_view_ids[] = $view_id;
			}
		}

		$unique_view_ids = array_unique( $related_view_ids );

		return array_values( $unique_view_ids );
	}

	/**
	 * @param WP_Post|null $post
	 */
	protected function print_embed_meta_box( $post ): void {
		$is_published = $post instanceof WP_Post &&
			'publish' === $post->post_status;

		if ( $is_published ) {
			$this->print_shortcode_postbox( $post );

			return;
		}

		$message = $this->get_unpublished_message();

		echo esc_html( $message );
	}

	protected function get_unpublished_message(): string {
		$singular_name    = $this->layouts_cpt
			->labels()
			->singular_name();
		// translators: %s - singular name of the CPT.
		$message_template = __( 'Your %s shortcode is available after publishing.', 'acf-views' );

		return sprintf( $message_template, $singular_name );
	}

	protected function print_shortcode_postbox( WP_Post $post ): void {
		$view_data            = $this->layouts_settings_storage->get( $post->post_name );
		$short_view_unique_id = $view_data->get_unique_id( true );
		$title                = get_the_title( $post );
		$is_internal_usage    = $view_data->is_for_internal_usage_only();
		$postbox              = $this->get_postbox();

		$postbox->print_postbox_shortcode(
			$short_view_unique_id,
			false,
			$this->layouts_cpt,
			$title,
			false,
			$is_internal_usage
		);
	}

	protected function print_groups_meta_box( WP_Post $wp_post ): void {
		$view_data = $this->layouts_settings_storage->get( $wp_post->post_name );

		$this->print_related_groups_meta_box( $view_data );
	}

	protected function print_views_meta_box( WP_Post $wp_post ): void {
		$view_data = $this->layouts_settings_storage->get( $wp_post->post_name );

		$this->print_related_views_meta_box( $view_data );
	}

	protected static function link_with_js_hover_tag( string $url, string $title ): Html_Tag {
		return tag( 'a' )
			->attributes(
				array(
					'href'        => $url,
					'target'      => '_blank',
					'style'       => array( 'transition' => 'all .3s ease' ),
					'onMouseOver' => "this.style.filter='brightness(30%)'",
					'onMouseOut'  => "this.style.filter='brightness(100%)'",
				)
			)
			->content(
				$title
			);
	}

	/**
	 * @return Html_Node[]
	 */
	protected static function compose_link_nodes( string $url, string $title, bool $is_last ): array {
		$link = self::link_with_js_hover_tag( $url, $title );

		return $is_last ?
			array( $link ) :
			array( $link, ', ' );
	}
}
