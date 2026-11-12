<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime;

use Org\Wplake\Advanced_Views\Assets\Runtime\Inline_Asset\Inline_Asset_Printer;
use Org\Wplake\Advanced_Views\Assets\Runtime\Script\Interactivity_Loader;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Asset_Loader;

defined( 'ABSPATH' ) || exit;

class Front_Asset_Loader extends Actor_Base implements Actor {
	private Library_Asset_Loader $library_asset_loader;
	private Interactivity_Loader $interactivity_loader;
	private Inline_Asset_Printer $inline_asset_loader;

	public function __construct(
		Library_Asset_Loader $library_asset_loader,
		Interactivity_Loader $interactivity_loader,
		Inline_Asset_Printer $inline_asset_loader
	) {
		$this->library_asset_loader = $library_asset_loader;
		$this->interactivity_loader = $interactivity_loader;
		$this->inline_asset_loader  = $inline_asset_loader;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// the enqueue must happen before the footer scripts are printed (priority 20).
		self::add_action( 'wp_footer', array( $this, 'enqueue_file_assets' ) );
		// printing contains ob_get_clean, so must be executed after all other scripts.
		self::add_action( 'wp_footer', array( $this, 'print_inline_assets' ), 9999 );
	}

	public function enqueue_file_assets(): void {
		$this->library_asset_loader->enqueue_assets();
		$this->interactivity_loader->enqueue_module();
	}

	public function print_inline_assets(): void {
		$this->inline_asset_loader->print_assets();
	}
}
