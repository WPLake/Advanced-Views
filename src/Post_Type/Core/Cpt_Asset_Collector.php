<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Cpt_Component_Composer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;

defined( 'ABSPATH' ) || exit;

final class Cpt_Asset_Collector {
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
		$this->add_css( $cpt_settings );
		$this->add_js( $cpt_settings );
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

	protected function add_css( Cpt_Settings $cpt_settings ): void {
		$css_code         = $cpt_settings->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY );
		$trimmed_css_code = trim( $css_code );
		$is_filled        = strlen( $trimmed_css_code ) > 0;
		$is_external      = false === $cpt_settings->is_css_internal();

		if ( $is_filled && $is_external ) {
			$unique_id = $cpt_settings->get_unique_id();

			$this->css_code[ $unique_id ] = $css_code;
		}
	}

	protected function add_js( Cpt_Settings $cpt_settings ): void {
		$js_code   = Cpt_Component_Composer::compose_js( $cpt_settings );
		$is_filled = strlen( $js_code ) > 0;

		if ( $is_filled ) {
			$unique_id = $cpt_settings->get_unique_id();

			if ( $cpt_settings->is_wp_interactivity_in_use() ) {
				$this->is_interactivity_required = true;
			}

			$this->js_code[ $unique_id ] = $js_code;
		}
	}

	protected function activate_libraries(Cpt_Settings $cpt_settings ): void {
		$libraries = $this->library_container->get_all();

		foreach ( $libraries as $library ) {
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
