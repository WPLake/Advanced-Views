<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Library;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;

defined( 'ABSPATH' ) || exit;

final class JS_Library_Loader {
	private Asset_Resolver $asset_resolver;
	/**
	 * @var array{strategy?: string, in_footer?: bool, fetchpriority?: string}
	 */
	private array $script_args;

	public function __construct( Asset_Resolver $asset_resolver ) {
		$this->asset_resolver = $asset_resolver;
		$this->script_args    = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);
	}

	/**
	 * @param array{strategy?: string, in_footer?: bool, fetchpriority?: string} $script_args
	 */
	public function set_script_args( array $script_args ): self {
		$this->script_args = $script_args;

		return $this;
	}

	public function enqueue_pattern( JS_Library $pattern ): void {
		$this->enqueue_local_scripts( $pattern );
		$this->enqueue_external_scripts( $pattern );

		self::localize_scripts( $pattern );
	}

	protected function enqueue_local_scripts( JS_Library $pattern ): void {
		$version = $this->asset_resolver->get_version();
		$origin  = $pattern->get_js_origin();

		$js_handles = $pattern->get_active_js_handles();

		foreach ( $js_handles as $js_handle ) {
			$script_file = sprintf( 'js/front/%s.min.js', $js_handle );
			$script_url  = $this->asset_resolver->get_origin_url( $origin, $script_file );
			$wp_handle   = self::get_wp_handle( $js_handle );

			wp_enqueue_script(
				$wp_handle,
				$script_url,
				array(),
				$version,
				$this->script_args
			);
		}
	}

	protected function enqueue_external_scripts( JS_Library $pattern ): void {
		$version = $this->asset_resolver->get_version();

		$external_scripts = $pattern->get_external_scripts();

		foreach ( $external_scripts as $js_handle => $script ) {
			$deps      = array_map( array( self::class, 'get_wp_handle' ), $script['deps'] );
			$wp_handle = self::get_wp_handle( $js_handle );

			wp_enqueue_script(
				$wp_handle,
				$script['url'],
				$deps,
				$version,
				$this->script_args
			);
		}
	}

	protected static function localize_scripts( JS_Library $pattern ): void {
		$js_data = $pattern->get_js_data();

		foreach ( $js_data as $js_handle => $objects ) {
			$wp_handle = self::get_wp_handle( $js_handle );

			foreach ( $objects as $object_name => $value ) {
				wp_localize_script(
					$wp_handle,
					$object_name,
					$value
				);
			}
		}
	}

	protected static function get_wp_handle( string $handle ): string {
		$cpt_name = Hard_Layout_Cpt::cpt_name();

		return sprintf( '%s_%s', $cpt_name, $handle );
	}
}
