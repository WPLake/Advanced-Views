<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Instance;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Bridge\Controllers\Request_Controller;
use Psr\Container\ContainerInterface;
use WP_REST_Request;

/**
 * Gets the ajax and REST API responses from the Layout's controller.
 */
final class Layout_Request_Responder {
	/**
	 * @param mixed $controller
	 *
	 * @return array<string,mixed>
	 */
	public function get_ajax_response( $controller, ?ContainerInterface $container ): array {
		if ( $controller instanceof Request_Controller ) {
			$controller->set_container( $container );

			return $controller->get_ajax_response();
		}

		return array();
	}

	/**
	 * @param mixed $controller
	 *
	 * @return array<string,mixed>
	 */
	public function get_rest_api_response( WP_REST_Request $wprest_request, $controller, ?ContainerInterface $container ): array {
		if ( $controller instanceof Request_Controller ) {
			$controller->set_container( $container );

			return $controller->get_rest_api_response( $wprest_request );
		}

		return array();
	}
}
