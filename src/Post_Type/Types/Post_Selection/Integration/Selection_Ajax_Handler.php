<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Integration;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Factory;

final class Selection_Ajax_Handler extends Actor_Base implements Actor {
	private Selection_Cpt $selection_cpt;
	private Selection_Settings_Storage $selections_settings_storage;
	private Selection_Factory $selection_factory;

	public function __construct(
		Selection_Cpt $selection_cpt,
		Selection_Settings_Storage $selections_settings_storage,
		Selection_Factory $selection_factory
	) {
		$this->selection_cpt               = $selection_cpt;
		$this->selections_settings_storage = $selections_settings_storage;
		$this->selection_factory           = $selection_factory;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return wp_doing_ajax();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'wp_ajax_nopriv_advanced_views', array( $this, 'get_ajax_response' ) );
		self::add_action( 'wp_ajax_advanced_views', array( $this, 'get_ajax_response' ) );
	}

	public function get_ajax_response(): void {
		$selection_id = Query_Arguments::get_string_for_non_action(
			array( '_cardId', '_post-selection-id' ),
			Query_Arguments::SOURCE_REQUEST
		);

		$is_selection_request = strlen( $selection_id ) > 0;

		// otherwise it may be a Layout request.
		if ( $is_selection_request ) {
			$this->send_selection_response( $selection_id );
		}
	}

	protected function send_selection_response( string $selection_id ): void {
		$response = $this->get_selection_response( $selection_id );
		$json     = wp_json_encode( $response );

		echo $json;
		exit;
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function get_selection_response( string $selection_id ): array {
		$cpt_name  = $this->selection_cpt->cpt_name();
		$unique_id = $this->selections_settings_storage->get_unique_id_from_shortcode_id( $selection_id, $cpt_name );
		$is_found  = strlen( $unique_id ) > 0;

		if ( $is_found ) {
			return $this->selection_factory->get_ajax_response( $unique_id );
		}

		return array(
			'_error' => __( 'Post Selection ID is wrong', 'acf-views' ),
		);
	}
}
