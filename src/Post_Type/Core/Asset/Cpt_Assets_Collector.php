<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Asset;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Cpt_Component_Composer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;

defined( 'ABSPATH' ) || exit;

class Cpt_Assets_Collector {
	private Library_Container $library_container;
	/**
	 * @var array<string, string>
	 */
	private array $css_code;
	/**
	 * @var array<string, string>
	 */
	private array $js_code;
	private bool $is_interactivity_required;

	public function __construct( Library_Container $library_container ) {
		$this->library_container         = $library_container;
		$this->css_code                  = array();
		$this->js_code                   = array();
		$this->is_interactivity_required = false;
	}

	public function add_asset( Cpt_Settings $cpt_settings ): void {
		$unique_id = $cpt_settings->get_unique_id();
		$css_code  = $cpt_settings->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY );
		$js_code   = Cpt_Component_Composer::compose_js( $cpt_settings );

		if ( '' !== trim( $css_code ) &&
			false === $cpt_settings->is_css_internal() ) {
			$this->css_code[ $unique_id ] = $css_code;
		}

		if ( strlen( $js_code ) > 0 ) {
			if ( $cpt_settings->is_wp_interactivity_in_use() ) {
				$this->is_interactivity_required = true;
			}

			$this->js_code[ $unique_id ] = $js_code;
		}

		$this->activate_libraries( $cpt_settings );
	}

	/**
	 * @return array<string, string> raw, not minified
	 */
	public function get_css_code(): array {
		return $this->css_code;
	}

	public function is_interactivity_required(): bool {
		return $this->is_interactivity_required;
	}

	/**
	 * @return array<string, string> raw (already wrapped into the web component), not minified
	 */
	public function get_js_code(): array {
		return $this->js_code;
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
