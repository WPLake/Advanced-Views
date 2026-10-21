<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Template\Html\Tags_Renderer;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Settings_Migrator;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
abstract class External_Storage_Tab extends Cpt_Table_Tab {

	const KEY_RESULT_ITEMS  = '';
	const KEY_RESULT_GROUPS = '';

	private Cpt_Settings_Storage $cpt_settings_storage;
	private Field_Provider_Cluster $provider_cluster;
	private Cpt_Settings_Migrator $cpt_settings_migrator;
	private Logger $logger;

	public function __construct(
		Cpt_Table $cpt_table,
		Cpt_Settings_Storage $cpt_settings_storage,
		Field_Provider_Cluster $provider_cluster,
		Cpt_Settings_Migrator $cpt_settings_migrator,
		Logger $logger
	) {
		parent::__construct( $cpt_table );

		$this->cpt_settings_storage  = $cpt_settings_storage;
		$this->provider_cluster      = $provider_cluster;
		$this->cpt_settings_migrator = $cpt_settings_migrator;
		$this->logger                = $logger;
	}

	abstract protected function get_cpt_data( string $unique_id ): Cpt_Settings;

	abstract protected function is_layout_item( Cpt_Settings $cpt_settings ): bool;

	abstract protected function get_title_field_name(): string;

	/**
	 * @param array<string,string> $field_values
	 */
	protected function import_cpt_data( string $unique_id, array $field_values ): ?Import_Result {
		$data_json_string = $field_values['data.json'] ?? '';
		/**
		 * @var string[]|null $data_json
		 */
		$data_json = json_decode( $data_json_string, true );

		if ( ! is_array( $data_json ) ||
			array() === $data_json ) {
			$this->logger->warning(
				'Import CPT Data skipped: invalid data.json file',
				array(
					'unique_id' => $unique_id,
				)
			);

			return null;
		}

		if ( key_exists( 'data.json', $field_values ) ) {
			unset( $field_values['data.json'] );
		}

		// 1. get item, maybe it's already exists (then we'll override it)
		$cpt_data = $this->cpt_settings_storage->get( $unique_id );

		$title = string( $data_json, $this->get_title_field_name() );

		// 2. insert if missing
		$cpt_data = false === $cpt_data->isLoaded() ?
			$this->cpt_settings_storage->create_new( 'publish', $title, null, $unique_id ) :
			$cpt_data;

		if ( null === $cpt_data ) {
			$this->logger->warning(
				'Import CPT Data skipped: fail to insert a post',
				array(
					'unique_id' => $unique_id,
				)
			);

			return null;
		}

		// 3. load all the old data.
		// It'll also override the unique id if the instance is just made, that's right as id kept the same
		$cpt_data->load( $cpt_data->get_post_id(), '', $data_json );

		// 4. set fs field values
		foreach ( $field_values as $file_field => $value ) {
			$this->cpt_settings_storage->get_fs_fields()->set_fs_field( $cpt_data, $file_field, $value );
		}

		// 5. perform upgrades (if items were created with the old plugin version)
		$this->cpt_settings_migrator->migrate_cpt_settings( $cpt_data );

		// 6. save
		$this->cpt_settings_storage->save( $cpt_data );

		// 7. import related meta groups (if present)
		$related_groups_import_result = $this->provider_cluster->import_related_group_files( $field_values );

		$import_result = new Import_Result();
		$import_result->add_unique_id( $unique_id );
		$import_result->merge_related_groups_import_result( $related_groups_import_result );

		$this->logger->debug(
			'Import CPT Data done',
			array(
				'unique_id' => $unique_id,
			)
		);

		return $import_result;
	}

	protected function get_logger(): Logger {
		return $this->logger;
	}

	protected function maybe_show_import_result_message(): void {
		$post_type = Query_Arguments::get_string_for_non_action( 'post_type' );

		if ( $this->get_cpt_name() !== $post_type ) {
			return;
		}

		$result_items = Query_Arguments::get_string_for_non_action( static::KEY_RESULT_ITEMS );

		if ( '' === $result_items ) {
			return;
		}

		// result groups argument is optional, as can be no related items.
		$result_groups = Query_Arguments::get_string_for_non_action( static::KEY_RESULT_GROUPS );

		$import_result = new Import_Result();
		$import_result->from_query_string( $result_items, $result_groups );

		$views_count = 0;
		$cards_count = 0;

		foreach ( $import_result->get_unique_ids() as $unique_id ) {
			// views and cards have different storages,
			// while in this class we have only the single one.
			$cpt_data = $this->get_cpt_data( $unique_id );

			if ( $this->is_layout_item( $cpt_data ) ) {
				++$views_count;
			} else {
				++$cards_count;
			}
		}

		$grouped_meta_group_links = array();

		foreach ( $import_result->get_related_groups_import_result()->get_group_ids() as $vendor_name => $group_ids ) {
			$grouped_meta_group_links[ $vendor_name ] = array();
			foreach ( $group_ids as $group_id ) {
				$group_link_data = $this->provider_cluster->get_group_link_by_group_id( $group_id, $vendor_name );

				if ( null === $group_link_data ) {
					continue;
				}

				$grouped_meta_group_links[ $vendor_name ][] = array(
					'url'   => $group_link_data['url'],
					'title' => $group_link_data['title'],
				);
			}
		}

		$layout_links = array();
		$card_links   = array();

		foreach ( $import_result->get_unique_ids() as $unique_id ) {
			// views and cards have different storages,
			// while in this class we have only the single one.
			$cpt_data = $this->get_cpt_data( $unique_id );
			$link     = array(
				'url'   => $cpt_data->get_edit_post_link(),
				'title' => $cpt_data->title,
			);

			if ( $this->is_layout_item( $cpt_data ) ) {
				$layout_links[] = $link;
			} else {
				$card_links[] = $link;
			}
		}

		$paragraphs   = array();
		$paragraphs[] = array(
			array( 'tag' => 'p' ),
			__( 'Success!', 'acf-views' ),
		);

		if ( $views_count > 0 ) {
			$paragraphs[] = $this->imported_items_paragraph( $views_count );
			$paragraphs[] = $this->links_paragraph( $layout_links );
		}

		if ( $cards_count > 0 ) {
			$paragraphs[] = $this->imported_items_paragraph( $cards_count );
			$paragraphs[] = $this->links_paragraph( $card_links );
		}

		foreach ( $grouped_meta_group_links as $vendor_name => $group_links ) {
			$group_links_count = count( $group_links );

			$paragraphs[] = array(
				array( 'tag' => 'p' ),
				sprintf(
							'%s %s %s %s',
							$group_links_count,
							ucfirst( $vendor_name ),
							_n( 'group', 'groups', $group_links_count, 'acf-views' ),
							// translators: x items successfully imported.
							__( 'successfully imported:', 'acf-views' )
						),
			);
			$paragraphs[] = $this->links_paragraph( $group_links );
		}

		Tags_Renderer::print_tags(
			array(
				array(
					'tag'   => 'div',
					'attrs' => array( 'class' => 'notice notice-success' ),
				),
				$paragraphs,
			)
		);
	}

	protected function get_cpt_data_storage(): Cpt_Settings_Storage {
		return $this->cpt_settings_storage;
	}

	protected function get_provider_cluster(): Field_Provider_Cluster {
		return $this->provider_cluster;
	}

	public function maybe_show_action_result_message(): void {
		$this->maybe_show_import_result_message();
	}

	/**
	 * @return Tag_Chain
	 */
	protected function imported_items_paragraph( int $items_count ): array {
		return array(
			array( 'tag' => 'p' ),
			sprintf(
						'%s %s %s',
						$items_count,
						_n( 'Item', 'Items', $items_count, 'acf-views' ),
						// translators: x items successfully imported.
						__( 'successfully imported:', 'acf-views' )
					),
		);
	}

	/**
	 * @param array<array{url:string,title:string}> $links
	 *
	 * @return Tag_Chain
	 */
	protected function links_paragraph( array $links ): array {
		$members = array();

		foreach ( $links as $link ) {
			// the 'br' goes between the links.
			if ( array() !== $members ) {
				$members[] = array( 'tag' => 'br' );
			}

			$members[] = array(
				array(
					'tag'   => 'a',
					'attrs' => array(
						'href'   => esc_url( $link['url'] ),
						'target' => '_blank',
					),
				),
				$link['title'],
			);
		}

		return array( array( 'tag' => 'p' ), $members );
	}
}
