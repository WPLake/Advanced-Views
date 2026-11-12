<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;

defined( 'ABSPATH' ) || exit;

class Cpt_Assets_Collector {
	private Tailwind_Integrator $tailwind_integrator;
	private Interactivity_Loader $interactivity_loader;
	private Library_Container $library_container;
	/**
	 * @var array<string, string>
	 */
	private array $css_code;
	/**
	 * @var array<string, string>
	 */
	private array $js_code;
	/**
	 * @var array<string,true>
	 */
	private array $tailwind_asset_ids;

	public function __construct( Tailwind_Integrator $tailwind_integrator, Interactivity_Loader $interactivity_loader, Library_Container $library_container ) {
		$this->tailwind_integrator  = $tailwind_integrator;
		$this->interactivity_loader = $interactivity_loader;
		$this->library_container    = $library_container;
		$this->css_code             = array();
		$this->js_code              = array();
		$this->tailwind_asset_ids   = array();
	}

	public function add_asset( Cpt_Settings $cpt_settings ): void {
		$unique_id  = $cpt_settings->get_unique_id();
		$css_source = $cpt_settings->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY );
		$js_source  = $cpt_settings->get_js_code();

		$css_code = Code_Minifier::minify_css( $css_source );
		$js_code  = Code_Minifier::minify_js( $js_source );

		if ( strlen( $css_code ) > 0 &&
			false === $cpt_settings->is_css_internal() ) {
			$this->css_code[ $unique_id ] = $css_code;

			$this->mark_tailwind_asset( $unique_id, $css_source );
		}

		if ( strlen( $js_code ) > 0 ) {
			if ( $cpt_settings->is_wp_interactivity_in_use() ) {
				$this->interactivity_loader->require_interactivity();
			}

			$this->js_code[ $unique_id ] = Web_Component_Builder::build_component_js( $cpt_settings, $js_code );
		}

		$this->activate_libraries( $cpt_settings );
	}

	/**
	 * @return array<string, string>
	 */
	public function get_css_code(): array {
		return $this->css_code;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_js_code(): array {
		return $this->js_code;
	}

	public function is_tailwind_asset( string $unique_id ): bool {
		return key_exists( $unique_id, $this->tailwind_asset_ids );
	}

	public function clear_tailwind_assets(): void {
		$this->tailwind_asset_ids = array();
	}

	protected function mark_tailwind_asset( string $unique_id, string $css_source ): void {
		if ( $this->tailwind_integrator->is_tailwind( $css_source ) ) {
			$this->tailwind_asset_ids[ $unique_id ] = true;

			return;
		}

		unset( $this->tailwind_asset_ids[ $unique_id ] );
	}

	protected function activate_libraries( Cpt_Settings $cpt_settings ): void {
		foreach ( $this->library_container->get_all() as $library ) {
			if ( $library instanceof Layout_Library &&
				$cpt_settings instanceof Layout_Settings ) {
				$library->maybe_activate_layout( $cpt_settings );
			}

			if ( $library instanceof Selection_Library &&
				$cpt_settings instanceof Post_Selection_Settings ) {
				$library->maybe_activate_selection( $cpt_settings );
			}
		}
	}
}
