<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Pods;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Settings_Vendor_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;
use WP_Post;

class Pods_Integration extends Settings_Vendor_Integration {

	private Pods_Data_Vendor $pods_data_vendor;

	public function __construct(
		Item_Settings $item_settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Field_Provider_Cluster $provider_cluster,
		Layout_Save_Actions $layouts_cpt_save_actions,
		Layout_Factory $layout_factory,
		Pods_Data_Vendor $pods_data_vendor,
		Layout_Shortcode $layout_shortcode,
		Cpt_Theme_Settings $settings,
		Layouts_Cpt $layouts_cpt
	) {
		parent::__construct(
			$item_settings,
			$layouts_settings_storage,
			$provider_cluster,
			$layouts_cpt_save_actions,
			$layout_factory,
			$pods_data_vendor,
			$layout_shortcode,
			$settings,
			$layouts_cpt
		);

		$this->pods_data_vendor = $pods_data_vendor;
	}

	public function add_tab_to_meta_group(): void {
		$append_related_block = function ( string $output, string $view_file ): string {
			$setup_position = strpos( $view_file, 'pods/ui/admin/setup-edit.php' );

			if ( ! is_int( $setup_position ) ) {
				return $output;
			}

			$id  = Query_Arguments::get_string_for_non_action( 'id' );
			$pod = $this->load_pod( $id );

			if ( ! is_object( $pod ) ) {
				return $output;
			}

			$pod_name = $this->get_pod_name( $pod );

			$container_attributes = array(
				'class' => array( 'advanced-views', 'postbox-container' ),
				'style' => array(
					'width'  => '70%',
					'margin' => '100px 0 0',
				),
			);

			$tags = array(
				array(
					'tag'   => 'div',
					'attrs' => $container_attributes,
				),
				fn() => $this->print_related_postbox( $pod_name, $pod ),
			);

			$related_block = Tags_Renderer::render_tags( $tags );

			return $output . $related_block;
		};

		self::add_filter( 'pods_view_output', $append_related_block, 10, 2 );
	}

	public function add_column_to_list_table(): void {
		$add_views_column = function ( array $options ): array {
			$page = Query_Arguments::get_string_for_non_action( 'page' );

			if ( 'pods' === $page ) {
				$options['fields']           = $this->get_array_arg( 'fields', $options );
				$options['fields']['manage'] = $this->get_array_arg( 'manage', $options['fields'] );
				$options['data']             = $this->get_array_arg( 'data', $options );

				$options['fields']['manage']['acf_views'] = array(
					'label' => __( 'Assigned to View', 'acf-views' ),
					'width' => '20%',
					'type'  => 'raw',
				);

				foreach ( $options['data'] as &$item ) {
					if ( false === is_array( $item ) ) {
						continue;
					}

					// post, page, taxonomy.
					$name = $this->get_string_arg( 'name', $item );

					$pod_object    = $item['pod_object'] ?? null;
					$related_views = $this->get_related_acf_views( $name, $pod_object );

					ob_start();
					$this->print_related_acf_views( null, true, $related_views );

					$item['acf_views'] = ob_get_clean();
				}
			}

			return $options;
		};

		self::add_filter( 'pods_ui_pre_init', $add_views_column );
	}

	public function get_vendor_name(): string {
		return Pods_Data_Vendor::NAME;
	}

	protected function get_vendor_post_type(): string {
		return '_pods_group';
	}

	/**
	 * @return mixed[]
	 */
	protected function get_group_fields( WP_Post $wp_post ): array {
		if ( ! function_exists( 'pods_api' ) ) {
			return array();
		}

		$pods_api = pods_api();

		if ( false === is_object( $pods_api ) ||
			false === method_exists( $pods_api, 'load_group' ) ) {
			return array();
		}

		$group_args = array(
			'id' => $wp_post->ID,
		);
		$wp_post    = $pods_api->load_group( $group_args );

		if ( false === is_object( $wp_post ) ||
			false === method_exists( $wp_post, 'get_fields' ) ||
			false === method_exists( $wp_post, 'get_parent_object' ) ) {
			return array();
		}

		$pod = $wp_post->get_parent_object();
		if ( false === is_object( $pod ) ||
			false === method_exists( $pod, 'get_args' ) ) {
			return array();
		}

		$pod_args = $pod->get_args();
		$pod_args = is_array( $pod_args ) ?
			$pod_args :
			array();
		$pod_name = $this->get_string_arg( 'name', $pod_args );

		$fields     = array();
		$pod_fields = $wp_post->get_fields();

		foreach ( $pod_fields as $field ) {
			if ( false === is_object( $field ) ||
				false === method_exists( $field, 'get_args' ) ) {
				continue;
			}

			$field_args = $field->get_args();
			$field_args = is_array( $field_args ) ?
				$field_args :
				array();

			// to be used in fillFieldIdAndType.
			$field_args['_pod_name'] = $pod_name;

			$fields[] = $field_args;
		}

		return $fields;
	}

	protected function load_pod( string $id ): ?object {
		if ( false === function_exists( 'pods_api' ) ||
			0 === strlen( $id ) ) {
			return null;
		}

		$pods_api = pods_api();

		if ( false === is_object( $pods_api ) ||
			false === method_exists( $pods_api, 'load_pod' ) ) {
			return null;
		}

		$pod_args = array(
			'id' => $id,
		);
		$pod      = $pods_api->load_pod( $pod_args );

		return is_object( $pod ) && method_exists( $pod, 'get_args' ) ?
			$pod :
			null;
	}

	protected function get_pod_name( object $pod ): string {
		$args = method_exists( $pod, 'get_args' ) ?
			$pod->get_args() :
			array();
		$args = is_array( $args ) ?
			$args :
			array();

		return $this->get_string_arg( 'name', $args );
	}

	/**
	 * @param mixed[] $field
	 */
	protected function get_group_key_by_from_post( WP_Post $wp_post, array $field ): string {
		// filled in $this->getGroupFields() method.
		$pod_type = $this->get_string_arg( '_pod_name', $field );

		$group_id = $this->pods_data_vendor->get_pods_group_id( $pod_type, $wp_post->post_name );

		return $this->pods_data_vendor->get_group_key( $group_id );
	}

	/**
	 * @param array<string,mixed> $field
	 */
	protected function fill_field_id_and_type( array $field, string &$field_id, string &$field_type ): void {
		$field_id = $this->get_string_arg( 'name', $field );
		// filled in $this->getGroupFields() method.
		$pod_type = $this->get_string_arg( '_pod_name', $field );

		$field_type = $this->get_string_arg( 'type', $field );
		$field_id   = $this->pods_data_vendor->get_pods_field_id( $pod_type, $field_id );
	}

	/**
	 * @param object $pod
	 */
	protected function print_related_postbox( string $pod_name, $pod ): void {
		$postbox_attributes = array(
			'class' => array( 'postbox', 'pods-no-toggle' ),
			'style' => array( 'padding' => '0 20px 20px' ),
		);
		$heading_attributes = array( 'class' => 'advanced-views__heading' );
		$related_views      = $this->get_related_acf_views( $pod_name, $pod );

		$print_heading_text = function (): void {
			$title = __( 'Advanced Views', 'acf-views' );

			echo esc_html( $title );
		};

		$tags = array(
			array(
				'tag'   => 'div',
				'attrs' => $postbox_attributes,
			),
			array(
				array(
					array(
						'tag'   => 'h4',
						'attrs' => $heading_attributes,
					),
					$print_heading_text,
				),
				fn() => $this->print_related_acf_views( null, false, $related_views ),
				fn() => $this->print_add_new_links_for_groups( $pod ),
			),
		);

		Tags_Renderer::print_tags( $tags );
	}

	/**
	 * @param object $pod
	 */
	protected function print_add_new_links_for_groups( $pod ): void {
		if ( method_exists( $pod, 'get_groups' ) ) {
			$groups = $pod->get_groups();

			echo '<div style="display: flex;gap:10px;flex-wrap:wrap;margin:20px 0 0;">';

			foreach ( $groups as $group ) {
				if ( false === is_object( $group ) ||
					false === method_exists( $group, 'get_args' ) ) {
					continue;
				}

				$group_info = $group->get_args();
				$group_info = is_array( $group_info ) ?
					$group_info :
					array();

				$group_title = $this->get_string_arg( 'label', $group_info );
				$link_title  = sprintf( ' %s', $group_title );
				$group_id    = $this->get_int_arg( 'id', $group_info );

				$this->print_add_new_link( $group_id, $link_title );
			}

			echo '</div>';
		}
	}

	/**
	 * @param mixed $pod
	 *
	 * @return Layout_Settings[]
	 */
	protected function get_related_acf_views( string $pod_name, $pod ): array {
		if ( false === is_object( $pod ) ||
			false === method_exists( $pod, 'get_groups' ) ) {
			return array();
		}

		$groups = $pod->get_groups();

		$views = array();

		foreach ( $groups as $group ) {
			if ( false === is_object( $group ) ||
				false === method_exists( $group, 'get_args' ) ) {
				return array();
			}

			$group_info = $group->get_args();
			$group_info = is_array( $group_info ) ?
				$group_info :
				array();

			$group_id  = $this->get_string_arg( 'name', $group_info );
			$group_id  = $this->pods_data_vendor->get_pods_group_id( $pod_name, $group_id );
			$group_key = $this->pods_data_vendor->get_group_key( $group_id );

			$views_storage = $this->get_views_data_storage();
			$group_views   = $views_storage->get_all_with_meta_group_in_use( $group_key );

			$views = array_merge( $views, $group_views );
		}

		// several groups may have the same View, so we need to remove duplicates
		// it works, as ViewData for the same id has the same instance.
		$unique_views = array_unique( $views, SORT_REGULAR );

		return array_values( $unique_views );
	}
}
