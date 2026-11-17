<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Library\Library_Asset_Collector;
use WP_REST_Request;

abstract class Instance_Factory {
	protected Cpt_Asset_Collector $assets_collector;
	protected Library_Asset_Collector $library_asset_collector;

	public function __construct( Cpt_Asset_Collector $assets_collector, Library_Asset_Collector $library_asset_collector ) {
		$this->assets_collector        = $assets_collector;
		$this->library_asset_collector = $library_asset_collector;
	}

	/**
	 * @return array<string,string>
	 */
	abstract public function get_template_fields( Cpt_Theme_Settings $theme_settings ): array;

	public function resolve_template_field_engine( string $field_name, Cpt_Theme_Settings $theme_settings ): string {
		$template_fields = $this->get_template_fields( $theme_settings );

		if ( key_exists( $field_name, $template_fields ) ) {
			return $template_fields[ $field_name ];
		}

		return '';
	}

	/**
	 * @return array<string,mixed>
	 */
	abstract protected function get_template_variables_for_validation( string $unique_id ): array;

	/**
	 * @param mixed[]|null $variables
	 *
	 * @return mixed[]
	 */
	public function get_autocomplete_variables( string $unique_id, ?array $variables = null ): array {
		$variables_for_validation = $variables ?? $this->get_template_variables_for_validation( $unique_id );

		foreach ( $variables_for_validation as $key => $value ) {
			if ( is_array( $value ) ) {
				$variables_for_validation[ $key ] = $this->get_autocomplete_variables( $unique_id, $value );
				continue;
			}

			// override the default value, we don't need to transfer 'fake' data to the front.
			$variables_for_validation[ $key ] = 'value';
		}

		return $variables_for_validation;
	}

	/**
	 * @return array<string,mixed>
	 */
	abstract public function get_rest_api_response( string $unique_id, WP_REST_Request $wprest_request ): array;
}
