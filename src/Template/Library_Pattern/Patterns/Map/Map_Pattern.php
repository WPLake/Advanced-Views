<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Map;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\Layout_Pattern_Base;

defined( 'ABSPATH' ) || exit;

class Map_Pattern extends Layout_Pattern_Base {
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

	public function get_js_data(): array {
		if ( ! $this->is_enabled_js_handle( 'acf-views-maps' ) ) {
			return array();
		}

		return array(
			'acf-views-maps' => array(
				'acfViewsMaps' => $this->maps,
			),
		);
	}

	public function get_external_scripts(): array {
		if ( ! $this->is_enabled_js_handle( 'acf-views-maps' ) ) {
			return array();
		}

		$api_data = apply_filters( 'acf/fields/google_map/api', array() );
		$key      = $api_data['key'] ?? '';
		$key      = ( '' === $key &&
						function_exists( 'acf_get_setting' ) ) ?
			acf_get_setting( 'google_api_key' ) :
			$key;

		return array(
			// the dependency makes sure it's loaded only after plugin's maps.min.js.
			'google-maps' => array(
				'url'  => sprintf( 'https://maps.googleapis.com/maps/api/js?key=%s&callback=acfViewsGoogleMaps', $key ),
				'deps' => array( 'acf-views-maps' ),
			),
		);
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields      = $this->get_target_fields( $layout_settings );
		$is_with_google_map = false;

		foreach ( $target_fields as $map_field ) {
			$map_type = $map_field->get_field_meta()->get_type();

			if ( 'open_street_map' !== $map_type ) {
				$is_with_google_map = true;
				$is_inner_target    = $this->is_google_map_selector_inner( $map_field );
				$this->maps[]       = $layout_settings->get_item_selector( $map_field, 'map', $is_inner_target );
			}
		}

		// only google map requires it.
		if ( $is_with_google_map ) {
			$this->enable_js_handle( 'acf-views-maps' );
		}
	}
}
