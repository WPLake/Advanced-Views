<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;

final class Layout_Ajax_Handler extends Actor_Base implements Actor {
	private Layout_Cpt $layout_cpt;
	private Layout_Settings_Storage $layouts_settings_storage;
	private Layout_Factory $layout_factory;

	public function __construct(
		Layout_Cpt $layout_cpt,
		Layout_Settings_Storage $layouts_settings_storage,
		Layout_Factory $layout_factory
	) {
		$this->layout_cpt               = $layout_cpt;
		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->layout_factory           = $layout_factory;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return wp_doing_ajax();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'wp_ajax_nopriv_advanced_views', array( $this, 'get_ajax_response' ) );
		self::add_action( 'wp_ajax_advanced_views', array( $this, 'get_ajax_response' ) );
	}

	public function get_ajax_response(): void {
		$layout_id = Query_Arguments::get_string_for_non_action(
			array( '_viewId', '_layout-id' ),
			Query_Arguments::SOURCE_REQUEST
		);

		if ( 0 === strlen( $layout_id ) ) {
			// it may be a Selection request.
			return;
		}

		$unique_id = $this->layouts_settings_storage->get_unique_id_from_shortcode_id(
			$layout_id,
			$this->layout_cpt->cpt_name()
		);

		if ( 0 === strlen( $unique_id ) ) {
			$error_response = array(
				'_error' => __( 'Layout id is wrong', 'acf-views' ),
			);

			echo wp_json_encode( $error_response );
			exit;
		}

		$response = $this->layout_factory->get_ajax_response( $unique_id );

		echo wp_json_encode( $response );
		exit;
	}
}
