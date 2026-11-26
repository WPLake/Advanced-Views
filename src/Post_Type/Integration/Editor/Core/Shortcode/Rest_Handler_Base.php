<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Instance_Factory;
use WP_REST_Request;

/**
 * Serves the public REST route of a CPT type: resolves the instance by the shortcode id and returns its response.
 */
abstract class Rest_Handler_Base extends Actor_Base implements Actor {
	private Public_Cpt $public_cpt;
	private Cpt_Settings_Storage $cpt_settings_storage;
	private Instance_Factory $instance_factory;

	public function __construct(
		Public_Cpt $public_cpt,
		Cpt_Settings_Storage $cpt_settings_storage,
		Instance_Factory $instance_factory
	) {
		$this->public_cpt           = $public_cpt;
		$this->cpt_settings_storage = $cpt_settings_storage;
		$this->instance_factory     = $instance_factory;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
	}

	public function register_rest_route(): void {
		foreach ( $this->public_cpt->rest_route_names() as $route_name ) {
			$route_pattern = $route_name . '/(?P<unique_id>[a-z0-9]+)';
			$route_args    = $this->get_rest_route_args();

			register_rest_route( Plugin::REST_NAMESPACE, $route_pattern, $route_args );
		}
	}

	abstract protected function get_unique_id_prefix(): string;

	/**
	 * @return mixed[]
	 */
	protected function get_rest_route_args(): array {
		$validate_unique_id = fn( $param ): bool => $this->is_unique_id_valid( $param );
		$get_response       = fn( WP_REST_Request $wprest_request ): array => $this->get_response( $wprest_request );

		$route_args = array(
			'unique_id' => array(
				'validate_callback' => $validate_unique_id,
			),
		);

		// permission_callback: available to all by default.
		return array(
			'methods'             => 'POST',
			'args'                => $route_args,
			'permission_callback' => fn(): bool => true,
			'callback'            => $get_response,
		);
	}

	/**
	 * @param mixed $param
	 */
	protected function is_unique_id_valid( $param ): bool {
		$is_scalar_id = is_string( $param ) || is_numeric( $param );

		if ( $is_scalar_id ) {
			$short_id  = (string) $param;
			$post_type = $this->public_cpt->cpt_name();
			$unique_id = $this->cpt_settings_storage->get_unique_id_from_shortcode_id( $short_id, $post_type );

			return strlen( $unique_id ) > 0;
		}

		return false;
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function get_response( WP_REST_Request $wprest_request ): array {
		$short_unique_id = $wprest_request->get_param( 'unique_id' );

		if ( is_string( $short_unique_id ) ) {
			$unique_id = $this->get_unique_id_prefix() . $short_unique_id;

			return $this->instance_factory->get_rest_api_response( $unique_id, $wprest_request );
		}

		// already validated above - only reachable if validate_callback let a non-string id through.
		return array();
	}
}
