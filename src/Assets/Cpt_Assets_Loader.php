<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

use Org\Wplake\Advanced_Views\Assets\Rework\Assets_Output_Injector;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Loader;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Loader;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider;

defined( 'ABSPATH' ) || exit;

class Cpt_Assets_Loader extends Actor_Base implements Actor {
	private Assets_Output_Injector $output_injector;
	private Library_Container $library_container;
	private Script_Loader $script_loader;
	private Style_Loader $style_loader;

	public function __construct( Assets_Output_Injector $output_injector, Library_Container $library_container, Script_Loader $script_loader, Style_Loader $style_loader ) {
		$this->output_injector   = $output_injector;
		$this->library_container = $library_container;
		$this->script_loader     = $script_loader;
		$this->style_loader      = $style_loader;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'wp_footer', array( $this, 'enqueue_assets' ) );
		// print_assets() contains ob_get_clean, so must be executed after all other scripts.
		self::add_action( 'wp_footer', array( $this->output_injector, 'print_assets' ), 9999 );
		self::add_action( 'wp_head', array( $this->output_injector, 'print_styles_stub' ) );
		// don't use 'get_header', as it doesn't work in blocks theme.
		self::add_action( 'template_redirect', array( $this->output_injector, 'start_buffering' ) );
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

				$this->style_loader->enqueue_styles( $name, $style_provider );
			}
		}
	}
}
