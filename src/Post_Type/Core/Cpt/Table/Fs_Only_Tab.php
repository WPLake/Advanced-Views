<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Fs_Only_Tab extends Cpt_Table_Tab {

	const NAME               = 'fs_only';
	const KEY_PREFIX         = 'acf-views-sync-';
	const KEY_BATCH_ACTION   = self::KEY_PREFIX . 'items';
	const KEY_SINGLE_ACTION  = self::KEY_PREFIX . 'id';
	const KEY_RESULT_MESSAGE = self::KEY_PREFIX . 'result-ids';

	private Cpt_Settings_Storage $cpt_settings_storage;
	protected Html_Renderer $html_renderer;

	public function __construct( Cpt_Table $cpt_table, Cpt_Settings_Storage $cpt_settings_storage, Html_Renderer $html_renderer, Row_Title_Printer $row_title_printer ) {
		parent::__construct( $cpt_table, $row_title_printer );

		$this->cpt_settings_storage = $cpt_settings_storage;
		$this->html_renderer        = $html_renderer;
	}

	protected function get_tab(): ?Tab_Data {
		$fs_only_items_count = $this->cpt_settings_storage->get_fs_only_items_count(
			$this->get_cpt_table()->get_current_search_value()
		);

		// do not show tab when there are no FS-only items
		// (but still show if the tab is active, it may be a search request that gives empty result).
		if ( 0 === $fs_only_items_count &&
			$this->get_cpt_table()->get_current_tab() !== self::NAME ) {
			return null;
		}

		$is_active_tab = self::NAME === $this->get_cpt_table()->get_current_tab();

		$items = $is_active_tab ?
			$this->cpt_settings_storage->get_fs_only_items(
				$this->get_cpt_table()->get_current_search_value(),
				$this->get_cpt_table()->get_current_page_number(),
				$this->get_pagination_per_page()
			) :
			array();

		$tab_data = new Tab_Data( $this );
		$tab_data->set_name( self::NAME );
		$tab_data->set_label( __( 'FS only', 'acf-views' ) );

		$print_description = function (): void {
			$read_more_label = __( 'Read more', 'acf-views' );

			$read_more_link = tag( 'a' )
				->attributes(
					array(
						'target' => '_blank',
						'href'   => 'https://docs.advanced-views.com/features/file-system-storage',
					)
				)
				->content(
					$read_more_label
				);

			$tags = array(
				__(
					'The "FS only" tab displays a list of items that do not have a record in the WordPress posts table and cannot be edited using the UI.',
					'acf-views'
				),
				tag( 'br' ),
				__(
					'If you need to amend such an item, you should "sync" it using the "sync" row or a bulk action.',
					'acf-views'
				),
				tag( 'br' ),
				__(
					'After doing so, the items will become editable using the UI (but the item data will still be stored in FS).',
					'acf-views'
				),
				' ',
				$read_more_link,
			);

			$this->html_renderer->print_html( ...$tags );
		};

		$tab_data->set_description_callback( $print_description );
		$tab_data->set_label_in_brackets( (string) $fs_only_items_count );
		$tab_data->set_bulk_actions( $this->get_bulk_actions() );
		$tab_data->set_total_items_count( $fs_only_items_count );
		$tab_data->set_items( $items );

		return $tab_data;
	}

	/**
	 * @return array<string, string> key => label
	 */
	public function get_bulk_actions(): array {
		return array(
			self::KEY_BATCH_ACTION => __( 'Sync', 'acf-views' ),
		);
	}

	public function maybe_perform_actions(): void {
		$unique_ids = $this->get_action_unique_ids( self::KEY_SINGLE_ACTION, self::KEY_BATCH_ACTION );

		if ( array() === $unique_ids ||
			! Avf_User::can_manage() ) {
			return;
		}

		foreach ( $unique_ids as $unique_id ) {
			// on batch action (e.g. for template validation) user can select 'sync' for non-FS-only items
			// we must ignore them.
			if ( false === $this->cpt_settings_storage->is_fs_only_item( $unique_id ) ) {
				continue;
			}

			$cpt_data = $this->cpt_settings_storage->get( $unique_id );

			$this->cpt_settings_storage->get_db_management()->make_post_for_fs_only_item( $cpt_data );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					self::KEY_RESULT_MESSAGE => implode( ',', $unique_ids ),
					'post_type'              => $this->get_cpt_name(),
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}

	public function maybe_show_action_result_message(): void {
		$post_type = Query_Arguments::get_string_for_non_action( 'post_type' );
		$ids       = Query_Arguments::get_string_for_non_action( self::KEY_RESULT_MESSAGE );

		if ( $this->get_cpt_name() !== $post_type ||
			'' === $ids ) {
			return;
		}

		$ids = explode( ',', $ids );

		$item_links = array();

		foreach ( $ids as $unique_id ) {
			$cpt_data = $this->cpt_settings_storage->get( $unique_id );

			// the 'br' goes between the links.
			if ( array() !== $item_links ) {
				$item_links[] = tag( 'br' );
			}

			$edit_link = $cpt_data->get_edit_post_link();

			$link_tag = tag( 'a' )
				->attributes(
					array(
						'href'   => $edit_link,
						'target' => '_blank',
					)
				)
				->content(
					$cpt_data->title
				);

			$item_links[] = $link_tag;
		}

		$synced_amount = count( $ids );
		$synced_label  = __( 'items successfully synced', 'acf-views' );
		$synced_text   = sprintf( '%d %s', $synced_amount, $synced_label );

		$synced_message = tag( 'p' )
			->content(
				$synced_text
			);
		$links_message  = tag( 'p' )
			->content( ...$item_links );

		$tags = tag( 'div' )
			->attributes( array( 'class' => 'notice notice-success' ) )
			->content(
				$synced_message,
				$links_message
			);

		$this->html_renderer->print_html( $tags );
	}

	public function print_row_title( Tab_Data $tab_data, Cpt_Settings $cpt_settings ): void {
		$url = add_query_arg(
			array(
				self::KEY_SINGLE_ACTION => $cpt_settings->get_unique_id(),
				'_wpnonce'              => wp_create_nonce( 'bulk-posts' ),
			)
		);
		$this->row_title_printer->print_row_title(
			$cpt_settings->title,
			'sync',
			$url,
			__( 'Sync', 'acf-views' )
		);
	}
}
