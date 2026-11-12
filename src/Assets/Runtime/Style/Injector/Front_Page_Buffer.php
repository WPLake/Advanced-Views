<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style\Injector;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;

defined( 'ABSPATH' ) || exit;

class Front_Page_Buffer extends Actor_Base implements Actor {
	private ?int $level;

	public function __construct() {
		$this->level = null;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// don't use 'get_header', as it doesn't work in blocks theme.
		self::add_action( 'template_redirect', array( $this, 'start' ) );
	}

	public function start(): void {
		ob_start();
		$this->level = ob_get_level();
	}

	public function is_started(): bool {
		return is_int( $this->level );
	}

	/**
	 * Returns the buffered page and closes our buffer.
	 */
	public function take_content(): string {
		// close previous buffers. Some plugins may not close, if detect that ob_get_level() is another than was
		// e.g. 'lightbox-photoswipe'.
		while ( ob_get_level() > (int) $this->level ) {
			ob_end_flush();
		}

		return (string) ob_get_clean();
	}

	public function flush_own(): void {
		// do not close the buffer, if it's not ours
		// (then ours will be closed automatically with the end of script execution).
		if ( $this->is_started() &&
			ob_get_level() === $this->level ) {
			ob_end_flush();
		}
	}
}
