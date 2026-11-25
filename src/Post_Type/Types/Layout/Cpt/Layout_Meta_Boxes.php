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

	protected function get_cpt_name(): string {
		return Hard_Layout_Cpt::cpt_name();
	}

	protected function link_with_js_hover_tag( string $url, string $title ): Html_Tag {
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
	 * @return string[]
	 */
	protected function get_related_view_unique_ids( Layout_Settings $layout_settings ): array {
		$related_view_ids = array();

		foreach ( $layout_settings->items as $item ) {
			if ( '' === $item->field->acf_view_id ) {
				continue;
			}

			$related_view_ids[] = $item->field->acf_view_id;
		}

		return array_values( array_unique( $related_view_ids ) );
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

		if ( array() === $used_meta_group_ids ) {
			$message = __( 'No assigned ACF Groups.', 'acf-views' );

			return $is_skip_not_found_message ?
				array() :
				array( $message );
		}

		$nodes            = array();
		$group_last_index = count( $used_meta_group_ids ) - 1;
		$counter          = - 1;

		foreach ( $used_meta_group_ids as $group_id ) {
			++$counter;

			$group_link = $this->provider_cluster->get_group_link_by_group_id( $group_id );

			if ( null === $group_link ) {
				continue;
			}

			$nodes[] = $this->link_with_js_hover_tag(
				$group_link['url'],
				$group_link['title']
			);

			if ( $counter !== $group_last_index ) {
				$nodes[] = ', ';
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

		if ( array() === $related_view_unique_ids ) {
			$message = sprintf(
				// translators: %s is the plural name of the CPT.
				__( 'No assigned %s.', 'acf-views' ),
				$this->layouts_cpt->labels()->plural_name()
			);

			return $is_skip_not_found_message ?
				array() :
				array( $message );
		}

		$nodes           = array();
		$last_item_index = count( $related_view_unique_ids ) - 1;
		$counter         = 0;

		foreach ( $related_view_unique_ids as $related_view_unique_id ) {
			$layout_settings = $this->layouts_settings_storage->get( $related_view_unique_id );

			$nodes[] = $this->link_with_js_hover_tag(
				$layout_settings->get_edit_post_link(),
				$layout_settings->title
			);

			if ( $counter !== $last_item_index ) {
				$nodes[] = ', ';
			}

			++$counter;
		}

		return $nodes;
	}

	public function add_meta_boxes(): void {

		add_meta_box(
			'acf-views_shortcode',
			__( 'Embed the Layout', 'acf-views' ),
			function ( $post ): void {
				if ( ! $post ||
					'publish' !== $post->post_status ) {
					echo esc_html(
						sprintf(
							// translators: %s - singular name of the CPT.
							__( 'Your %s shortcode is available after publishing.', 'acf-views' ),
							$this->layouts_cpt->labels()->singular_name()
						)
					);

					return;
				}

				$view_data            = $this->layouts_settings_storage->get( $post->post_name );
				$short_view_unique_id = $view_data->get_unique_id( true );

				$this->get_postbox()->print_postbox_shortcode(
					$short_view_unique_id,
					false,
					$this->layouts_cpt,
					get_the_title( $post ),
					false,
					$view_data->is_for_internal_usage_only()
				);
			},
			array(
				$this->get_cpt_name(),
			),
			'side',
			'high'
		);

		add_meta_box(
			'acf-views_related_groups',
			__( 'Field Groups', 'acf-views' ),
			function ( WP_Post $wp_post ): void {
				$view_data = $this->layouts_settings_storage->get( $wp_post->post_name );
				$this->print_related_groups_meta_box( $view_data );
			},
			array(
				$this->get_cpt_name(),
			),
			'side',
			'core'
		);

		add_meta_box(
			'acf-views_related_views',
			sprintf(
			// translators: %s is the plural name of the CPT.
				__( 'Nested %s', 'acf-views' ),
				$this->layouts_cpt->labels()->plural_name()
			),
			function ( WP_Post $wp_post ): void {
				$view_data = $this->layouts_settings_storage->get( $wp_post->post_name );

				$this->print_related_views_meta_box( $view_data );
			},
			array(
				$this->get_cpt_name(),
			),
			'side',
			'core'
		);

		parent::add_meta_boxes();
	}
}
