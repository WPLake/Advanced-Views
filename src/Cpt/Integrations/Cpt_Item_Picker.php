<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt\Integrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Cpt\Base\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Base\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;

/**
 * Item-picking logic shared by every editor integration -
 * one instance per CPT, owning its "refresh items" REST route.
 */
final class Cpt_Item_Picker extends Hookable implements Hooks_Interface {
	const CATEGORY = 'advanced-views';
	// one route template for every CPT - each instance's actual endpoint is this suffixed with its own cpt_name(),
	// so Layout's and Selection's registrations never collide despite sharing the same constant.
	const REST_ROUTE_PREFIX = 'cpt-item-picker';

	private Cpt_Settings_Storage $settings_storage;
	private Public_Cpt $cpt;

	public function __construct( Cpt_Settings_Storage $settings_storage, Public_Cpt $cpt ) {
		$this->settings_storage = $settings_storage;
		$this->cpt              = $cpt;
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		if ( $route_detector->is_admin_route() ) {
			self::add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
		}
	}

	public function register_rest_route(): void {
		register_rest_route(
			Plugin::REST_NAMESPACE,
			$this->get_route_name(),
			array(
				'methods'             => 'GET',
				'permission_callback' => fn(): bool => Avf_User::can_manage(),
				/**
				 * @return array<string,array{title:string,editUrl:string}>
				 */
				'callback'            => fn(): array => $this->get_items(),
			),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public function get_flat_items(): array {
		$list = array();

		if ( Avf_User::can_manage() ) {
			foreach ( $this->settings_storage->get_db_management()->get_post_ids() as $unique_id => $post_id ) {
				$list[ $unique_id ] = $this->settings_storage->get( $unique_id )->title;
			}
		}

		return $list;
	}

	/**
	 * Extends get_flat_items() by adding a resolved editUrl.
	 *
	 * @return array<string,array{title:string,editUrl:string}>
	 */
	public function get_items(): array {
		$list = array();

		foreach ( $this->get_flat_items() as $unique_id => $title ) {
			$edit_url = $this->settings_storage->get( $unique_id )
												->get_edit_post_link( 'raw' );

			$list[ $unique_id ] = array(
				'title'   => $title,
				'editUrl' => $edit_url,
			);
		}

		return $list;
	}

	/**
	 * Picker data used in the cptItemPicker.ts
	 *
	 * @return array{items:array<string,array{title:string,editUrl:string}>,newItemUrl:string,itemsRestUrl:string,canManage:bool,itemLabel:string}
	 */
	public function get_js_data(): array {
		return array(
			'items'        => $this->get_items(),
			'newItemUrl'   => admin_url( sprintf( 'post-new.php?post_type=%s', $this->cpt->cpt_name() ) ),
			'itemsRestUrl' => sprintf( '/%s/%s', Plugin::REST_NAMESPACE, $this->get_route_name() ),
			'canManage'    => Avf_User::can_manage(),
			'itemLabel'    => $this->cpt->labels()->singular_name(),
		);
	}

	protected function get_route_name(): string {
		return sprintf( '%s/%s', self::REST_ROUTE_PREFIX, $this->cpt->cpt_name() );
	}
}
