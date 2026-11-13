<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Cpt_Component_Composer;

defined( 'ABSPATH' ) || exit;

final class Cpt_Asset_Collector {
	/**
	 * @var array<string, Cpt_Settings>
	 */
	private array $cpt_settings;

	public function __construct() {
		$this->cpt_settings = array();
	}

	public function add_asset( Cpt_Settings $cpt_settings ): void {
		$this->cpt_settings[ $cpt_settings->get_unique_id() ] = $cpt_settings;
	}

	/**
	 * @return array<string, string> raw, not minified
	 */
	public function get_css_code(): array {
		$css_codes = array();

		foreach ( $this->cpt_settings as $unique_id => $cpt_settings ) {
			$css_code = $this->compose_css( $cpt_settings );

			if ( '' !== $css_code ) {
				$css_codes[ $unique_id ] = $css_code;
			}
		}

		return $css_codes;
	}

	public function is_interactivity_required(): bool {
		foreach ( $this->cpt_settings as $cpt_settings ) {
			if ( $cpt_settings->has_js_code() &&
				$cpt_settings->is_wp_interactivity_in_use() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array<string, string> raw (already wrapped into the web component), not minified
	 */
	public function get_js_code(): array {
		$js_codes = array();

		foreach ( $this->cpt_settings as $unique_id => $cpt_settings ) {
			$js_code = Cpt_Component_Composer::compose_js( $cpt_settings );

			if ( '' !== $js_code ) {
				$js_codes[ $unique_id ] = $js_code;
			}
		}

		return $js_codes;
	}

	protected function compose_css( Cpt_Settings $cpt_settings ): string {
		$css_code    = $cpt_settings->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY );
		$is_filled   = '' !== trim( $css_code );
		$is_external = false === $cpt_settings->is_css_internal();

		return $is_filled && $is_external ?
			$css_code :
			'';
	}
}
