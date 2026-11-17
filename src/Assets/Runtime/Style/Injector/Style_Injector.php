<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style\Injector;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Reloader_Root_Marker;

defined( 'ABSPATH' ) || exit;

final class Style_Injector extends Actor_Base implements Actor {
	const STUB            = '<!--advanced-views:styles-->';
	const CUSTOM_LOCATION = '<!--advanced-views:styles/custom-location-->';

	private Front_Page_Buffer $page_buffer;
	private Reloader_Root_Marker $reloader_root_marker;

	public function __construct( Front_Page_Buffer $page_buffer, Reloader_Root_Marker $reloader_root_marker ) {
		$this->page_buffer          = $page_buffer;
		$this->reloader_root_marker = $reloader_root_marker;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'wp_head', array( $this, 'print_styles_stub' ) );
	}

	public function print_styles_stub(): void {
		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::STUB;
	}

	/**
	 * Prints the buffered page with the styles inside, or just the styles, if the page wasn't buffered.
	 * The live reloader needs its styles root on the page even when there are no styles yet.
	 */
	public function inject( string $css ): void {
		$is_page_required = strlen( $css ) > 0 ||
							$this->reloader_root_marker->is_active();

		if ( $is_page_required ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->resolve_output( $css );

			return;
		}

		// nothing to inject: the buffered page must still be released.
		$this->page_buffer->flush_own();
	}

	protected function resolve_output( string $css ): string {
		if ( $this->page_buffer->is_started() ) {
			$page_content = $this->page_buffer->take_content();

			return $this->place( $page_content, $css );
		}

		// if the buffer wasn't started, then 'template_redirect' hook wasn't called,
		// so the styles are printed here, instead of the header.
		return $css;
	}

	/**
	 * The styles go to the custom location, if the page has one, otherwise to the stub (in the head).
	 */
	protected function place( string $page, string $css ): string {
		$location_position   = strpos( $page, self::CUSTOM_LOCATION );
		$has_custom_location = is_int( $location_position );

		if ( $has_custom_location ) {
			$marked_css = $this->reloader_root_marker->mark( $css );
			$page       = str_replace( self::CUSTOM_LOCATION, $marked_css, $page );
			$css        = '';
		}

		return str_replace( self::STUB, $css, $page );
	}
}
