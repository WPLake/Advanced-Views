<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Map\Asset_Providers;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider_Base;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

class Script_Provider extends Script_Provider_Base {
	protected const HANDLE = 'acf-views-maps';

	/**
	 * @var string[]
	 */
	private array $maps;

	public function __construct() {
		$this->maps = array();
	}

	public function enable(): void {
		$this->enable_handles( array( self::HANDLE ) );
	}

	/**
	 * @param string[] $maps
	 */
	public function set_maps( array $maps ): void {
		$this->maps = $maps;
	}

	public function get_script_variables(): array {
		return array(
			self::HANDLE => array(
				'acfViewsMaps' => $this->maps,
			),
		);
	}

	public function get_external_scripts(): array {
		$api_data = apply_filters( 'acf/fields/google_map/api', array() );
		$key      = string( $api_data, 'key' );
		$key      = ( '' === $key &&
						function_exists( 'acf_get_setting' ) ) ?
			string( acf_get_setting( 'google_api_key' ) ) :
			$key;

		return array(
			// the dependency makes sure it's loaded only after plugin's maps.min.js.
			'google-maps' => array(
				'url'  => sprintf( 'https://maps.googleapis.com/maps/api/js?key=%s&callback=acfViewsGoogleMaps', $key ),
				'deps' => array( self::HANDLE ),
			),
		);
	}
}
