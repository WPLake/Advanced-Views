<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Table;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Postbox_Printer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_Query;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\any;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Layouts_Cpt_Table extends Cpt_Table {
	const COLUMN_DESCRIPTION             = self::COLUMN_PREFIX . 'description';
	const COLUMN_SHORTCODE               = self::COLUMN_PREFIX . 'shortcode';
	const COLUMN_LAST_MODIFIED           = self::COLUMN_PREFIX . 'lastModified';
	const COLUMN_RELATED_GROUPS          = self::COLUMN_PREFIX . 'relatedGroups';
	const COLUMN_RELATED_POST_SELECTIONS = self::COLUMN_PREFIX . 'relatedPostSelections';

	private Postbox_Printer $postbox;
	private Layout_Meta_Boxes $layouts_cpt_meta_boxes;
	private Plugin_Cpt $selections_cpt;

	public function __construct(
		Layout_Settings_Storage $cpt_settings_storage,
		Layouts_Cpt $public_cpt,
		Postbox_Printer $postbox,
		Layout_Meta_Boxes $layouts_cpt_meta_boxes,
		Selections_Cpt $plugin_cpt,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $cpt_settings_storage, $public_cpt, $html_renderer );

		$this->postbox                = $postbox;
		$this->layouts_cpt_meta_boxes = $layouts_cpt_meta_boxes;
		$this->selections_cpt         = $plugin_cpt;
	}

	/**
	 * @param array<string, string> $columns
	 *
	 * @return array<string, string>
	 */
	public function get_sortable_columns( array $columns ): array {
		return array_merge(
			$columns,
			array(
				self::COLUMN_LAST_MODIFIED => self::COLUMN_LAST_MODIFIED,
			)
		);
	}

	public function add_sortable_columns_to_request( WP_Query $wp_query ): void {
		if ( ! is_admin() ) {
			return;
		}

		$order_by = $wp_query->get( 'orderby' );

		switch ( $order_by ) {
			case self::COLUMN_LAST_MODIFIED:
				$wp_query->set( 'orderby', 'post_modified' );
				break;
		}
	}

	/**
	 * @param array<string,string> $columns
	 *
	 * @return array<string,string>
	 */
	public function get_columns( array $columns ): array {
		unset( $columns['date'] );

		return array_merge(
			$columns,
			array(
				self::COLUMN_DESCRIPTION             => __( 'Description', 'acf-views' ),
				self::COLUMN_SHORTCODE               => __( 'Embed the Layout', 'acf-views' ),
				self::COLUMN_RELATED_GROUPS          => __( 'Assigned Field Group', 'acf-views' ),
				self::COLUMN_RELATED_POST_SELECTIONS => sprintf(
					// translators: %s - singular name of the CPT.
					__( 'Assigned to %s', 'acf-views' ),
					$this->selections_cpt->labels()->singular_name()
				),
				self::COLUMN_LAST_MODIFIED           => __( 'Last modified', 'acf-views' ),
			)
		);
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		parent::set_route_hooks( $route_detector );

		self::add_action( 'pre_get_posts', array( $this, 'add_sortable_columns_to_request' ) );

		self::add_filter(
			sprintf( 'manage_edit-%s_sortable_columns', $this->get_cpt_name() ),
			array( $this, 'get_sortable_columns' )
		);
	}

	protected function get_views_meta_boxes(): Layout_Meta_Boxes {
		return $this->layouts_cpt_meta_boxes;
	}

	/**
	 * @return Html_Node[]
	 */
	protected function column_nodes( string $short_column_name, Cpt_Settings $cpt_settings ): array {
		if ( $cpt_settings instanceof Layout_Settings ) {
			$column_resolvers = $this->get_column_resolvers( $cpt_settings );
			$column_resolver  = any( $column_resolvers, $short_column_name );

			if ( is_callable( $column_resolver ) ) {
				return $column_resolver();
			}
		}

		return array();
	}

	/**
	 * @return array<string, Closure(): Html_Node[]>
	 */
	protected function get_column_resolvers( Layout_Settings $view_data ): array {
		return array(
			self::COLUMN_DESCRIPTION             => fn() => array( $view_data->description ),
			self::COLUMN_SHORTCODE               => fn() =>
				$this->postbox->postbox_shortcode_nodes(
					$view_data->get_unique_id( true ),
					true,
					$this->public_plugin_cpt,
					$view_data->title,
					false,
					$view_data->is_for_internal_usage_only()
				),
			self::COLUMN_RELATED_GROUPS          => fn() =>
				$this->layouts_cpt_meta_boxes->related_groups_nodes( $view_data, true ),
			self::COLUMN_RELATED_POST_SELECTIONS => fn() =>
				$this->layouts_cpt_meta_boxes->related_acf_cards_nodes( $view_data, true ),
			self::COLUMN_LAST_MODIFIED           => function () use ( $view_data ): array {
				$post_id = $view_data->get_post_id();

				$post = 0 !== $post_id ?
					get_post( $post_id ) :
					null;

				return null !== $post ?
					array( explode( ' ', $post->post_modified )[0] ) :
					array();
			},
		);
	}

	protected function get_unique_id_prefix(): string {
		return Layout_Settings::UNIQUE_ID_PREFIX;
	}
}
