<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Script;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;

defined( 'ABSPATH' ) || exit;

class Interactivity_Loader extends Actor_Base implements Actor {
	const MODULE_ID = '@wordpress/interactivity';

	private bool $is_enqueued;

	public function __construct() {
		$this->is_enqueued = false;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_filter( 'script_module_loader_src', array( $this, 'catch_script_url' ), 10, 2 );
	}

	/**
	 * Enqueues the module, as in non-block themes it isn't enqueued by default.
	 */
	public function enqueue_module(): void {
		if ( function_exists( 'wp_enqueue_script_module' ) ) {
			$this->is_enqueued = true;

			wp_enqueue_script_module( self::MODULE_ID );
		}
	}

	/**
	 * Classic themes (WP 6.7+) have no way to enqueue the iApi script and get its import map printed,
	 * and a fixed url can't be used, as iApi script urls are versioned
	 * (see wp-includes/assets/script-modules-packages.min.php).
	 */
	public function catch_script_url( string $src, string $id ): string {
		if ( self::MODULE_ID === $id &&
			$this->is_enqueued ) {
			$this->is_enqueued = false;

			self::print_import_map( $src );
		}

		return $src;
	}

	protected static function print_import_map( string $script_url ): void {
		$imports   = array(
			self::MODULE_ID => $script_url,
		);
		$data      = array(
			'imports' => $imports,
		);
		$json_data = (string) wp_json_encode(
			$data,
			JSON_HEX_TAG | JSON_HEX_AMP
		);

		$attributes = array(
			'type' => 'importmap',
			'id'   => 'avf-importmap',
		);

		wp_print_inline_script_tag( $json_data, $attributes );
	}
}
