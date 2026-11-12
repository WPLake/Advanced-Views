<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Reloader_Root_Marker;

defined( 'ABSPATH' ) || exit;

class Inline_Style_Injector {
	const STUB            = '<!--advanced-views:styles-->';
	const CUSTOM_LOCATION = '<!--advanced-views:styles/custom-location-->';

	private Reloader_Root_Marker $reloader_root_marker;

	public function __construct( Reloader_Root_Marker $reloader_root_marker ) {
		$this->reloader_root_marker = $reloader_root_marker;
	}

	/**
	 * The styles go to the custom location, if the page has one, otherwise to the stub (in the head).
	 */
	public function place( string $page, string $css ): string {
		if ( false !== strpos( $page, self::CUSTOM_LOCATION ) ) {
			$marked_css = $this->reloader_root_marker->mark( $css );
			$page       = str_replace( self::CUSTOM_LOCATION, $marked_css, $page );
			$css        = '';
		}

		return str_replace( self::STUB, $css, $page );
	}
}
