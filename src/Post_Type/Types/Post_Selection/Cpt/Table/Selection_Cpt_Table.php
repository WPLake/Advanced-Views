<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Table;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_Post;
use WP_Query;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Selection_Cpt_Table extends Cpt_Table {
	const COLUMN_DESCRIPTION   = self::COLUMN_PREFIX . 'description';
	const COLUMN_RELATED_VIEW  = self::COLUMN_PREFIX . 'relatedView';
	const COLUMN_LAST_MODIFIED = self::COLUMN_PREFIX . 'lastModified';

	private Selection_Meta_Boxes $post_selections_cpt_meta_boxes;
	private Layout_Cpt $layout_cpt;

	public function __construct(
		Selection_Settings_Storage $post_selections_settings_storage,
		Selection_Cpt $public_cpt,
		Selection_Meta_Boxes $post_selections_cpt_meta_boxes,
		Layout_Cpt $layout_cpt,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $post_selections_settings_storage, $public_cpt, $html_renderer );

		$this->post_selections_cpt_meta_boxes = $post_selections_cpt_meta_boxes;
		$this->layout_cpt                     = $layout_cpt;
	}

	public function add_sortable_columns_to_request( WP_Query $wp_query ): void {
		$order_by         = $wp_query->get( 'orderby' );
		$is_modified_sort = is_admin() &&
							self::COLUMN_LAST_MODIFIED === $order_by;

		if ( $is_modified_sort ) {
			$wp_query->set( 'orderby', 'post_modified' );
		}
	}

	/**
	 * @param array<string,string> $columns
	 *
	 * @return array<string,string>
	 */
	public function get_columns( array $columns ): array {
		unset( $columns['date'] );

		$layout_labels = $this->layout_cpt->labels();
		$layout_name   = $layout_labels->singular_name();

		$own_columns = array(
			self::COLUMN_DESCRIPTION   => __( 'Description', 'acf-views' ),
			self::COLUMN_RELATED_VIEW  => sprintf(
				// translators: %s - singular name of the CPT.
				__( 'Related %s', 'acf-views' ),
				$layout_name
			),
			self::COLUMN_LAST_MODIFIED => __( 'Last modified', 'acf-views' ),
		);

		return array_merge( $columns, $own_columns );
	}

	/**
	 * @param array<string,string> $columns
	 *
	 * @return array<string,string>
	 */
	public function get_sortable_columns( array $columns ): array {
		return array_merge(
			$columns,
			array(
				self::COLUMN_LAST_MODIFIED => self::COLUMN_LAST_MODIFIED,
			)
		);
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		parent::set_route_hooks( $route_detector );

		self::add_action( 'pre_get_posts', array( $this, 'add_sortable_columns_to_request' ) );

		$cpt_name    = $this->get_cpt_name();
		$filter_name = sprintf( 'manage_edit-%s_sortable_columns', $cpt_name );

		self::add_filter( $filter_name, array( $this, 'get_sortable_columns' ) );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function column_nodes( string $short_column_name, Cpt_Settings $cpt_settings ): array {
		if ( $cpt_settings instanceof Post_Selection_Settings ) {
			$builders = array(
				self::COLUMN_DESCRIPTION   => fn() => array( $cpt_settings->description ),
				self::COLUMN_LAST_MODIFIED => fn() => $this->last_modified_nodes( $cpt_settings ),
				// without the not found message.
				self::COLUMN_RELATED_VIEW  => fn() => $this->post_selections_cpt_meta_boxes->related_acf_view_nodes( $cpt_settings, true ),
			);

			if ( key_exists( $short_column_name, $builders ) ) {
				return $builders[ $short_column_name ]();
			}
		}

		return array();
	}

	protected function get_cards_meta_boxes(): Selection_Meta_Boxes {
		return $this->post_selections_cpt_meta_boxes;
	}

	/**
	 * @return Html_Node[]
	 */
	protected function last_modified_nodes( Post_Selection_Settings $selection_settings ): array {
		$post_id = $selection_settings->get_post_id();

		$post = 0 !== $post_id ?
			get_post( $post_id ) :
			null;

		if ( $post instanceof WP_Post ) {
			$modified_parts = explode( ' ', $post->post_modified );

			return array( $modified_parts[0] );
		}

		return array();
	}

	protected function get_unique_id_prefix(): string {
		return Post_Selection_Settings::UNIQUE_ID_PREFIX;
	}
}
