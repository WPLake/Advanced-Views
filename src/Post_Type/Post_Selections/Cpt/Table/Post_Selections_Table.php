<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Postbox_Printer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_Query;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Post_Selections_Table extends Cpt_Table {
	const COLUMN_DESCRIPTION   = self::COLUMN_PREFIX . 'description';
	const COLUMN_SHORTCODE     = self::COLUMN_PREFIX . 'shortcode';
	const COLUMN_RELATED_VIEW  = self::COLUMN_PREFIX . 'relatedView';
	const COLUMN_LAST_MODIFIED = self::COLUMN_PREFIX . 'lastModified';

	private Postbox_Printer $postbox;
	private Selection_Meta_Boxes $post_selections_cpt_meta_boxes;
	private Layouts_Cpt $layout_cpt;

	public function __construct(
		Selection_Settings_Storage $post_selections_settings_storage,
		Selections_Cpt $public_cpt,
		Postbox_Printer $postbox,
		Selection_Meta_Boxes $post_selections_cpt_meta_boxes,
		Layouts_Cpt $layout_cpt,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $post_selections_settings_storage, $public_cpt, $html_renderer );

		$this->postbox                        = $postbox;
		$this->post_selections_cpt_meta_boxes = $post_selections_cpt_meta_boxes;
		$this->layout_cpt                     = $layout_cpt;
	}

	/**
	 * @return Html_Node[]
	 */
	protected function column_nodes( string $short_column_name, Cpt_Settings $cpt_settings ): array {
		if ( false === ( $cpt_settings instanceof Post_Selection_Settings ) ) {
			return array();
		}

		$card_data = $cpt_settings;

		switch ( $short_column_name ) {
			case self::COLUMN_DESCRIPTION:
				return array( $card_data->description );
			case self::COLUMN_SHORTCODE:
				return $this->postbox->postbox_shortcode_nodes(
					$card_data->get_unique_id( true ),
					true,
					$this->public_plugin_cpt,
					$card_data->title,
					true
				);
			case self::COLUMN_LAST_MODIFIED:
				$post_id = $card_data->get_post_id();

				$post = 0 !== $post_id ?
					get_post( $post_id ) :
					null;

				return null !== $post ?
					array( explode( ' ', $post->post_modified )[0] ) :
					array();
			case self::COLUMN_RELATED_VIEW:
				// without the not found message.
				return $this->post_selections_cpt_meta_boxes->related_acf_view_nodes( $card_data, true );
		}

		return array();
	}

	protected function get_cards_meta_boxes(): Selection_Meta_Boxes {
		return $this->post_selections_cpt_meta_boxes;
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
				self::COLUMN_DESCRIPTION   => __( 'Description', 'acf-views' ),
				self::COLUMN_SHORTCODE     => __( 'Embed the Post Selection', 'acf-views' ),
				self::COLUMN_RELATED_VIEW  => sprintf(
					// translators: %s - singular name of the CPT.
					__( 'Related %s', 'acf-views' ),
					$this->layout_cpt->labels()->singular_name()
				),
				self::COLUMN_LAST_MODIFIED => __( 'Last modified', 'acf-views' ),
			)
		);
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
		self::add_filter(
			sprintf( 'manage_edit-%s_sortable_columns', $this->get_cpt_name() ),
			array( $this, 'get_sortable_columns' )
		);
	}

	protected function get_unique_id_prefix(): string {
		return Post_Selection_Settings::UNIQUE_ID_PREFIX;
	}
}
