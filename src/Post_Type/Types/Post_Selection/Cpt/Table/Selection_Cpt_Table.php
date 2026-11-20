<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Table;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Cpt_Table;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_Post;
use WP_Post_Type;
use WP_Query;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Selection_Cpt_Table extends Cpt_Table {
	const COLUMN_ID            = self::COLUMN_PREFIX . 'id';
	const COLUMN_DESCRIPTION   = self::COLUMN_PREFIX . 'description';
	const COLUMN_POST_TYPES    = self::COLUMN_PREFIX . 'postTypes';
	const COLUMN_LAST_MODIFIED = self::COLUMN_PREFIX . 'lastModified';
	const COLUMN_CREATED       = self::COLUMN_PREFIX . 'created';

	public function __construct(
		Selection_Settings_Storage $post_selections_settings_storage,
		Selection_Cpt $public_cpt,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $post_selections_settings_storage, $public_cpt, $html_renderer );
	}

	public function add_sortable_columns_to_request( WP_Query $wp_query ): void {
		$order_by       = $wp_query->get( 'orderby' );
		$orderby_fields = array(
			self::COLUMN_LAST_MODIFIED => 'post_modified',
			self::COLUMN_CREATED       => 'post_date',
		);

		$is_sortable = is_admin() &&
						is_string( $order_by ) &&
						key_exists( $order_by, $orderby_fields );

		if ( $is_sortable ) {
			$wp_query->set( 'orderby', $orderby_fields[ $order_by ] );
		}
	}

	/**
	 * @param array<string,string> $columns
	 *
	 * @return array<string,string>
	 */
	public function get_columns( array $columns ): array {
		unset( $columns['date'] );

		$own_columns = array(
			self::COLUMN_POST_TYPES    => __( 'Post Types', 'acf-views' ),
			self::COLUMN_DESCRIPTION   => __( 'Description', 'acf-views' ),
			self::COLUMN_ID            => __( 'Unique ID', 'acf-views' ),
			self::COLUMN_LAST_MODIFIED => __( 'Last modified', 'acf-views' ),
			self::COLUMN_CREATED       => __( 'Created', 'acf-views' ),
		);

		return array_merge( $columns, $own_columns );
	}

	/**
	 * @param array<string,string> $columns
	 *
	 * @return array<string,string>
	 */
	public function get_sortable_columns( array $columns ): array {
		$sortable_columns = array(
			self::COLUMN_LAST_MODIFIED => self::COLUMN_LAST_MODIFIED,
			self::COLUMN_CREATED       => self::COLUMN_CREATED,
		);

		return array_merge( $columns, $sortable_columns );
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
				self::COLUMN_POST_TYPES    => fn() => array( self::post_type_labels( $cpt_settings ) ),
				self::COLUMN_DESCRIPTION   => fn() => array( $cpt_settings->description ),
				self::COLUMN_ID            => fn() => array( $cpt_settings->get_unique_id( true ) ),
				self::COLUMN_LAST_MODIFIED => fn() => self::date_nodes( $cpt_settings, 'post_modified' ),
				self::COLUMN_CREATED       => fn() => self::date_nodes( $cpt_settings, 'post_date' ),
			);

			if ( key_exists( $short_column_name, $builders ) ) {
				return $builders[ $short_column_name ]();
			}
		}

		return array();
	}

	protected function get_unique_id_prefix(): string {
		return Post_Selection_Settings::UNIQUE_ID_PREFIX;
	}

	/**
	 * @return Html_Node[]
	 */
	protected static function date_nodes( Post_Selection_Settings $selection_settings, string $post_field ): array {
		$post_id = $selection_settings->get_post_id();

		$post = 0 !== $post_id ?
			get_post( $post_id ) :
			null;

		if ( $post instanceof WP_Post ) {
			$date_parts = explode( ' ', $post->$post_field );

			return array( $date_parts[0] );
		}

		return array();
	}

	protected static function post_type_labels( Post_Selection_Settings $selection_settings ): string {
		$labels = array_map( array( self::class, 'post_type_label' ), $selection_settings->post_types );

		return implode( '; ', $labels );
	}

	protected static function post_type_label( string $post_type ): string {
		$post_type_object = get_post_type_object( $post_type );

		return $post_type_object instanceof WP_Post_Type ?
			$post_type_object->label :
			$post_type;
	}
}
