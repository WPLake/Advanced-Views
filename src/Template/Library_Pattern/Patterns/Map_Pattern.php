<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Assets\Resolver\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern_Base;

defined( 'ABSPATH' ) || exit;

class Map_Pattern extends Template_Pattern_Base {
	const NAME = 'acf-views-maps';

	/**
	 * @var string[]
	 */
	private array $maps;

	public function __construct( Asset_Resolver $asset_resolver, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $asset_resolver, $provider_cluster );

		$this->set_js_handles(
			array(
				'acf-views-maps' => false,
			)
		);

		$this->maps = array();
	}

	protected function is_google_map_selector_inner( Field_Settings $field_settings ): bool {
		return false;
	}

	public function enqueue_active(): string {
		$css_code = parent::enqueue_active();

		if ( false === $this->is_enabled_js_handle( 'acf-views-maps' ) ) {
			return $css_code;
		}

		$api_data = apply_filters( 'acf/fields/google_map/api', array() );
		$key      = $api_data['key'] ?? '';
		$key      = ( '' === $key &&
						function_exists( 'acf_get_setting' ) ) ?
			acf_get_setting( 'google_api_key' ) :
			$key;

		$maps_handle   = self::get_wp_handle( 'acf-views-maps' );
		$google_handle = self::get_wp_handle( 'google-maps' );
		$google_url    = sprintf( 'https://maps.googleapis.com/maps/api/js?key=%s&callback=acfViewsGoogleMaps', $key );
		$version       = $this->get_asset_resolver()->get_version();
		$script_args   = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		wp_localize_script( $maps_handle, 'acfViewsMaps', $this->maps );

		// setup deps, to make sure loaded only after plugin's maps.min.js.
		wp_enqueue_script( $google_handle, $google_url, array( $maps_handle ), $version, $script_args );

		return $css_code;
	}

	public function maybe_activate( Cpt_Settings $cpt_settings ): void {
		if ( $cpt_settings instanceof Layout_Settings ) {
			$target_fields      = $this->get_target_fields( $cpt_settings );
			$is_with_google_map = false;

			foreach ( $target_fields as $map_field ) {
				$map_type = $map_field->get_field_meta()->get_type();

				if ( 'open_street_map' !== $map_type ) {
					$is_with_google_map = true;
					$is_inner_target    = $this->is_google_map_selector_inner( $map_field );
					$this->maps[]       = $cpt_settings->get_item_selector( $map_field, 'map', $is_inner_target );
				}
			}

			// only google map requires it.
			if ( $is_with_google_map ) {
				$this->enable_js_handle( 'acf-views-maps' );
			}
		}
	}
}
