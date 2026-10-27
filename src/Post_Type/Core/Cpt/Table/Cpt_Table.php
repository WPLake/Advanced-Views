<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use WP_List_Table;
use WP_Post;
use WP_Post_Type;
use WP_Query;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
abstract class Cpt_Table extends Actor_Base implements Actor {
	const COLUMN_PREFIX = Hard_Layout_Cpt::NAME . '_';

	private Cpt_Settings_Storage $cpt_settings_storage;
	private string $cpt_name;
	/**
	 * @var Tab_Data[]
	 */
	private array $tabs;
	/**
	 * @var callable[]
	 */
	private array $add_tab_callbacks;
	private ?string $current_tab;
	private ?int $current_page_number;
	private ?string $current_search_value;
	private ?int $pagination_per_page;
	protected Public_Cpt $public_plugin_cpt;
	protected Html_Renderer $html_renderer;

	public function __construct( Cpt_Settings_Storage $cpt_settings_storage, Public_Cpt $public_cpt, Html_Renderer $html_renderer ) {
		$this->cpt_settings_storage = $cpt_settings_storage;
		$this->public_plugin_cpt    = $public_cpt;
		$this->cpt_name             = $public_cpt->cpt_name();
		$this->tabs                 = array();
		$this->add_tab_callbacks    = array();

		$this->current_tab          = null;
		$this->current_page_number  = null;
		$this->current_search_value = null;
		$this->pagination_per_page  = null;
		$this->html_renderer        = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_filter(
			sprintf( 'manage_%s_posts_columns', $this->get_cpt_name() ),
			array( $this, 'get_columns' ),
		);

		if ( $route_detector->is_cpt_admin_route( $this->cpt_name, Route_Detector::CPT_LIST ) ) {
			self::add_action( 'admin_init', array( $this, 'make_table_actions' ) );
			self::add_action( 'admin_notices', array( $this, 'show_action_result_message' ) );
			self::add_filter( 'admin_body_class', array( $this, 'add_acf_class_to_body' ) );
			self::add_filter( sprintf( 'views_edit-%s', $this->cpt_name ), array( $this, 'modify_table_header' ) );
		}

		self::add_action(
			sprintf( 'manage_%s_posts_custom_column', $this->cpt_name ),
			array( $this, 'printTableColumn' ),
			10,
			2
		);
		self::add_action( 'pre_get_posts', array( $this, 'add_post_name_to_search' ) );

		self::add_filter( 'post_row_actions', array( $this, 'get_row_actions' ), 10, 2 );
		self::add_filter( sprintf( 'bulk_actions-edit-%s', $this->cpt_name ), array( $this, 'get_bulk_table_actions' ) );
		self::add_filter( 'get_the_excerpt', array( $this, 'hide_excerpt_from_extended_list_view' ), 10, 2 );
	}

	abstract protected function get_unique_id_prefix(): string;

	abstract protected function print_column( string $short_column_name, Cpt_Settings $cpt_settings ): void;

	protected function get_action_clone(): string {
		return $this->cpt_name . '_clone';
	}

	protected function get_action_cloned(): string {
		return $this->cpt_name . '_cloned';
	}

	protected function maybe_clone_item(): void {
		$post_id = Query_Arguments::get_int_for_admin_action( $this->get_action_clone(), 'bulk-posts' );
		$post    = 0 !== $post_id ?
			get_post( $post_id ) :
			null;

		if ( null === $post ||
			$this->cpt_name !== $post->post_type ||
			! Avf_User::can_manage() ) {
			return;
		}

		$origin_cpt_data = $this->cpt_settings_storage->get( $post->post_name );
		$title           = $origin_cpt_data->title . ' ' . __( 'Clone', 'acf-views' );

		// 1. make the new instance
		$cpt_data = $this->cpt_settings_storage->create_new( 'draft', $title, (int) $post->post_author );

		if ( null === $cpt_data ) {
			return;
		}

		$new_unique_id = $cpt_data->get_unique_id();

		// 2. load all the data from the origin post
		$cpt_data->load( $cpt_data->get_post_id(), '', $origin_cpt_data->getFieldValues() );

		// 3. restore the data that shouldn't be used from the origin post
		$cpt_data->unique_id = $new_unique_id;
		$cpt_data->title     = $title;

		// 4. save
		$this->cpt_settings_storage->save( $cpt_data );

		wp_safe_redirect(
			$this->get_tab_url(
				'',
				array(
					$this->get_action_cloned() => '1',
				)
			)
		);
		exit;
	}

	protected function maybe_show_item_cloned_message(): void {
		$cloned_action = Query_Arguments::get_string_for_non_action( $this->get_action_cloned() );

		if ( '' === $cloned_action ) {
			return;
		}

		$message = __( 'Item successfully cloned.', 'acf-views' );

		$tags = tag( 'div' )
			->attributes( array( 'class' => 'notice notice-success' ) )
			->content(
				tag( 'p' )
					->content(
						$message
					)
			);

		$this->html_renderer->print_html( $tags );
	}

	protected function print_post_type_description( WP_Post_Type $wp_post_type ): void {
		$current_tab_data = $this->get_current_tab_data();

		$description_callback = null !== $current_tab_data ?
			$current_tab_data->get_description_callback() :
			null;

		if ( null !== $description_callback ) {
			$tags = tag( 'p' )
				->content(
					$description_callback
				);

			$this->html_renderer->print_html( $tags );

			return;
		}

		if ( '' !== $wp_post_type->description ) {
			$node = tag( 'p' )
				->content(
					// don't use esc_html as it contains links.
					// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					fn() => print( $wp_post_type->description )
				);

			$this->html_renderer->print_html( $node );
		}
	}

	/**
	 * @param string[] $views
	 *
	 * @return string[]
	 */
	protected function add_tabs( array $views ): array {
		global $wp_list_table, $wp_query;

		foreach ( $this->tabs as $tab ) {
			$is_tab_active = $tab->get_name() === $this->get_current_tab();

			$attrs = $is_tab_active ?
				array( 'class' => 'current' ) :
				array();

			$attrs['href'] = $this->get_tab_url( $tab->get_name() );

			$count = tag( 'span' )
				->attributes( array( 'class' => 'count' ) )
				->content(
					sprintf( '(%s)', $tab->get_label_in_brackets() )
				);

			$count_span = '' !== $tab->get_label_in_brackets() ?
				array(
					' ',
					$count,
				) :
				array();

			$tab_link = tag( 'a' )
				->attributes( $attrs )
				->content(
					$tab->get_label(),
					...$count_span
				);

			$views[ $tab->get_name() ] = $this->html_renderer->render_html( $tab_link );

			if ( false === $is_tab_active ) {
				continue;
			}

			// Modify table pagination args to match JSON data.
			$wp_list_table->set_pagination_args(
				array(
					'total_items' => $tab->get_total_items_count(),
					'total_pages' => ceil( $tab->get_total_items_count() / $tab->get_pagination_per_page() ),
					'per_page'    => $tab->get_pagination_per_page(),
				)
			);
			// At least one post is needed to render bulk drop-down.
			$wp_query->post_count = count( $tab->get_items() ) > 0 ?
				1 :
				0;
		}

		return $views;
	}

	protected function print_search_box_description( bool $is_custom_tab_active ): void {
		$label = false === $is_custom_tab_active ?
			__( 'Search for name, description, labels or id.', 'acf-views' ) :
			__( 'Search for name.', 'acf-views' );

		// only primary search is available for custom items.
		$tags = tag( 'style' )
			->content(
				fn() => print( sprintf( '#posts-filter .search-box::after{content:"%s";}', esc_html( $label ) ) )
			);

		$this->html_renderer->print_html( $tags );
	}

	/**
	 * @param array<string,string> $columns
	 *
	 * @return array<string,string>
	 */
	abstract public function get_columns( array $columns ): array;

	/**
	 * @param array<string, string|int> $extra_args
	 */
	public function get_tab_url( string $post_status, array $extra_args = array() ): string {
		$url = get_admin_url( null, sprintf( '/edit.php?post_type=%s', $this->cpt_name ) );

		// can be empty (we if we need to show the 'all' tab).
		if ( '' !== $post_status ) {
			$extra_args['post_status'] = $post_status;
		}

		if ( array() !== $extra_args ) {
			$url = add_query_arg( $extra_args, $url );
		}

		return $url;
	}

	/**
	 * @param array<string,string> $items
	 * @param array<string,string> $new_items
	 *
	 * @return array<string,string>
	 */
	public function insert_into_array_after_key( array $items, string $key, array $new_items ): array {
		$keys  = array_keys( $items );
		$index = array_search( $key, $keys, true );

		$pos = false === $index ?
			count( $items ) :
			$index + 1;

		return array_merge( array_slice( $items, 0, $pos ), $new_items, array_slice( $items, $pos ) );
	}

	public function add_post_name_to_search( WP_Query $wp_query ): void {
		$post_type = $wp_query->query_vars['post_type'] ?? '';

		if ( ! is_admin() ||
			$this->get_cpt_name() !== $post_type ||
			! $wp_query->is_main_query() ||
			! $wp_query->is_search() ) {
			return;
		}

		$search = $wp_query->query_vars['s'];

		if ( 13 !== strlen( $search ) ||
			false === preg_match( '/^[a-z0-9]+$/', $search ) ) {
			return;
		}

		$wp_query->set( 's', '' );
		$wp_query->set( 'name', $this->get_unique_id_prefix() . $search );
	}

	public function make_table_actions(): void {
		$this->maybe_clone_item();

		if ( $this->cpt_settings_storage->get_file_system()->is_active() ) {
			$this->cpt_settings_storage->delete_db_only_items();
			$this->cpt_settings_storage->rewrite_links_md_for_all_items();
		}
	}

	public function show_action_result_message(): void {
		$this->maybe_show_item_cloned_message();
	}

	/**
	 * @param array<string,string> $actions
	 *
	 * @return array<string,string>
	 */
	public function get_row_actions( array $actions, WP_Post $wp_post ): array {
		if ( $this->cpt_name !== $wp_post->post_type ) {
			return $actions;
		}

		$trash = str_replace(
			'>Trash<',
			sprintf( '>%s<', __( 'Delete', 'acf-views' ) ),
			$actions['trash'] ?? ''
		);

		// quick edit.
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['trash'] );

		$clone_link = $this->get_tab_url(
			'',
			array(
				$this->get_action_clone() => $wp_post->ID,
				'_wpnonce'                => wp_create_nonce( 'bulk-posts' ),
			)
		);

		$clone_label = __( 'Clone', 'acf-views' );

		$tags = tag( 'a' )
			->attributes( array( 'href' => $clone_link ) )
			->content(
				$clone_label
			);

		$actions['clone'] = $this->html_renderer->render_html( $tags );
		$actions['trash'] = $trash;

		return $actions;
	}

	/**
	 * @param string[] $views
	 *
	 * @return string[]
	 */
	public function modify_table_header( array $views ): array {
		foreach ( $this->add_tab_callbacks as $add_tab_callback ) {
			call_user_func( $add_tab_callback );
		}

		$views = $this->add_tabs( $views );

		$screen    = get_current_screen();
		$post_type = null !== $screen ?
			get_post_type_object( $screen->post_type ) :
			null;

		if ( null !== $post_type ) {
			$this->print_post_type_description( $post_type );
		}

		$current_tab_data = $this->get_current_tab_data();

		// do not replace if tab is active, but no items are available (to display the not found message).
		if ( null !== $current_tab_data &&
			count( $current_tab_data->get_items() ) > 0 ) {
			self::add_action( 'admin_footer', array( $this, 'replace_table_items_with_custom' ) );
		}

		$is_custom_tab_active = null !== $current_tab_data;
		$this->print_search_box_description( $is_custom_tab_active );

		return $views;
	}

	public function replace_table_items_with_custom(): void {
		$current_tab_data = $this->get_current_tab_data();

		if ( null === $current_tab_data ) {
			return;
		}

		$custom_table_items = $current_tab_data->get_items();

		global $wp_list_table;

		// Get table columns.
		$columns = $wp_list_table->get_columns();
		$hidden  = get_hidden_columns( $wp_list_table->screen );

		$rows = array();

		foreach ( $custom_table_items as $cpt_data ) {
			$cells = array();

			foreach ( $columns as $column_name => $column_label ) {
				$cells[] = $this->table_cell( $current_tab_data, $cpt_data, $column_name, $column_label, $hidden );
			}

			$rows[] = tag( 'tr' )
				->content( ...$cells );
		}

		// hide the origin items until the custom items are pasted via JS.
		$hide_style   = tag( 'style' )
			->content(
				fn() => print( '#the-list{opacity: 0; transition: opacity ease .1s;}' )
			);
		$rows_table   = tag( 'div' )
			->attributes( array( 'style' => array( 'display' => 'none' ) ) )
			->content(
				tag( 'table' )
					->content(
						tag( 'tbody' )
							->attributes( array( 'id' => 'acf-views-the-list' ) )
							->content( ...$rows )
					)
			);
		$paste_script = tag( 'script' )
			->attributes( array( 'type' => 'text/javascript' ) )
			->content(
				// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				fn() => print( 'jQuery(document).ready(function(){jQuery("#the-list").html(jQuery("#acf-views-the-list").children()).css("opacity","1");});' )
			);

		$this->html_renderer->print_html(
			$hide_style,
			$rows_table,
			$paste_script
		);
	}

	public function printTableColumn( string $column_name, int $post_id ): void {
		$unique_id = get_post( $post_id )->post_name ?? '';
		$cpt_data  = $this->cpt_settings_storage->get( $unique_id );

		$this->print_column( $column_name, $cpt_data );
	}

	/**
	 * @param array<string,string> $actions
	 *
	 * @return array<string,string>
	 */
	public function get_bulk_table_actions( array $actions ): array {
		$current_tab_data = $this->get_current_tab_data();

		if ( null === $current_tab_data ) {
			return $actions;
		}

		return $current_tab_data->get_bulk_actions();
	}

	public function hide_excerpt_from_extended_list_view( string $excerpt, WP_Post $wp_post ): string {
		if ( $this->cpt_name !== $wp_post->post_type ) {
			return $excerpt;
		}

		$request_uri  = Query_Arguments::get_string_for_non_action( 'REQUEST_URI', 'server' );
		$is_edit_page = false !== strpos( $request_uri, '/edit.php' );
		$post_type    = Query_Arguments::get_string_for_non_action( 'post_type' );

		if ( ! $is_edit_page ||
			$this->cpt_name !== $post_type ) {
			return $excerpt;
		}

		return '';
	}

	// add the ACF's class to the body to have a nice look of the list table.
	public function add_acf_class_to_body( string $classes ): string {
		return $classes . ' acf-internal-post-type';
	}

	public function get_cpt_name(): string {
		return $this->cpt_name;
	}

	public function add_tab( Tab_Data $tab_data ): void {
		$this->tabs[] = $tab_data;
	}

	public function get_current_tab(): string {
		if ( null === $this->current_tab ) {
			$this->current_tab = Query_Arguments::get_string_for_non_action( 'post_status' );
		}

		return $this->current_tab;
	}

	public function get_current_page_number(): int {
		if ( null === $this->current_page_number ) {
			$page_number = Query_Arguments::get_int_for_non_action( 'paged' );

			$this->current_page_number = 0 === $page_number ?
				1 :
				$page_number;
		}

		return $this->current_page_number;
	}

	public function get_current_search_value(): string {
		if ( null === $this->current_search_value ) {
			$s = Query_Arguments::get_string_for_non_action( 's' );

			$this->current_search_value = strtolower( $s );
		}

		return $this->current_search_value;
	}

	public function get_pagination_per_page(): int {
		if ( null === $this->pagination_per_page ) {
			global $wp_list_table;

			$default  = 10;
			$per_page = $default;

			if ( false !== ( $wp_list_table instanceof WP_List_Table ) ) {
				$per_page = $wp_list_table->get_pagination_arg( 'per_page' );
				$per_page = 0 === $per_page ?
					$default :
					$per_page;
			}

			$this->pagination_per_page = $per_page;
		}

		return $this->pagination_per_page;
	}

	public function get_current_tab_data(): ?Tab_Data {
		$current_tab = $this->get_current_tab();

		foreach ( $this->tabs as $tab ) {
			if ( $current_tab !== $tab->get_name() ) {
				continue;
			}

			return $tab;
		}

		return null;
	}

	public function add_new_tab_callback( callable $new_tab_callback ): void {
		$this->add_tab_callbacks[] = $new_tab_callback;
	}

	/**
	 * @param string[] $hidden_columns
	 */
	protected function table_cell(
		Tab_Data $tab_data,
		Cpt_Settings $cpt_settings,
		string $column_name,
		string $column_label,
		array $hidden_columns
	): Html_Tag {
		$is_checkbox = 'cb' === $column_name;
		$classes     = array(
			'cb'    => 'check-column',
			'title' => sprintf( '%s column-%1$s column-primary', $column_name ),
		);
		$classes     = array( $classes[ $column_name ] ?? sprintf( '%s column-%1$s', $column_label ) );

		if ( in_array( $column_name, $hidden_columns, true ) ) {
			$classes[] = 'hidden';
		}

		$contents = array(
			'cb'    => $this->checkbox_cell_content( $cpt_settings->get_unique_id(), $cpt_settings->title ),
			'title' => array( fn() => $tab_data->print_row_title( $cpt_settings ) ),
		);
		$content  = $contents[ $column_name ] ?? array( fn() => $this->print_column( $column_name, $cpt_settings ) );

		$tag_name = $is_checkbox ?
			'th' :
			'td';
		$colname  = $is_checkbox ?
			'' :
			$column_label;

		return tag( $tag_name )
			->attributes(
				array(
					'class'        => $classes,
					'data-colname' => $colname,
				)
			)
			->content( ...$content );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function checkbox_cell_content( string $unique_id, string $title ): array {
		$checkbox_id = sprintf( 'cb-select-%s', $unique_id );
		// translators: %s: item title.
		$select_label = __( 'Select %s', 'acf-views' );
		$select_text  = sprintf( $select_label, $title );

		$label = tag( 'label' )
			->attributes(
				array(
					'for'   => $checkbox_id,
					'class' => 'screen-reader-text',
				)
			)
			->content(
				$select_text
			);

		return array(
			$label,
			tag( 'input' )
				->attributes(
					array(
						'id'    => $checkbox_id,
						'type'  => 'checkbox',
						'value' => $unique_id,
						'name'  => 'post[]',
					)
				),
		);
	}
}
