<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;

class Assets_Enqueuer {
	/**
	 * Enqueues JS files and returns the CSS code to be printed inline.
	 */
	public static function enqueue( Assets_Location $location, Assets_Handles $handles ): string {
		$script_args = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		$version = $location->version();

		foreach ( $handles->js as $handle ) {
			$script_file = sprintf( 'js/front/%s.min.js', $handle );
			$wp_handle   = self::get_wp_handle( $handle );
			$script_url  = $location->url( $script_file );

			wp_enqueue_script( $wp_handle, $script_url, array(), $version, $script_args );
		}

		$css           = '';
		$wp_filesystem = WP_Filesystem_Factory::get_wp_filesystem();

		foreach ( $handles->css as $handle ) {
			$style_file = sprintf( 'css/front/%s.min.css', $handle );

			$style_path = $location->path( $style_file );

			$css .= (string) $wp_filesystem->get_contents( $style_path );
		}

		return $css;
	}

	protected static function get_wp_handle( string $handle ): string {
		return sprintf( '%s_%s', Hard_Layout_Cpt::cpt_name(), $handle );
	}
}
