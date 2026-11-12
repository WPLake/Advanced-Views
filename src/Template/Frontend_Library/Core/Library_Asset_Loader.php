<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Loader;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Collector;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider;

defined( 'ABSPATH' ) || exit;

class Library_Asset_Loader extends Actor_Base implements Actor {
	private Library_Container $library_container;
	private Script_Loader $script_loader;
	private Style_Collector $style_collector;

	public function __construct( Library_Container $library_container, Script_Loader $script_loader, Style_Collector $style_collector ) {
		$this->library_container = $library_container;
		$this->script_loader     = $script_loader;
		$this->style_collector   = $style_collector;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'wp_footer', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets(): void {
		foreach ( $this->library_container->get_all() as $library ) {
			$script_provider = $library->get_js_library();
			$style_provider  = $library->get_css_library();

			if ( $script_provider instanceof Script_Provider &&
				$script_provider->has_active_scripts() ) {
				$this->script_loader->enqueue_scripts( $script_provider );
			}

			if ( $style_provider instanceof Style_Provider &&
				$style_provider->has_active_styles() ) {
				$name = $library->get_name();

				$this->style_collector->enqueue_styles( $name, $style_provider );
			}
		}
	}
}
