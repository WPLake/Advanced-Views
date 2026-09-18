<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Groups\Meta_Field_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;

defined( 'ABSPATH' ) || exit;

class Meta_Field_Settings_Integration extends Acf_Integration {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( string $target_cpt_name, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $target_cpt_name );

		$this->provider_cluster = $provider_cluster;
	}

	protected function set_field_choices(): void {
		self::add_filter(
			'acf/load_field/name=' . Meta_Field_Settings::getAcfFieldName( Meta_Field_Settings::FIELD_GROUP ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_group_choices( true );

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Meta_Field_Settings::getAcfFieldName( Meta_Field_Settings::FIELD_FIELD_KEY ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_field_choices( true );

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Meta_Field_Settings::getAcfFieldName( Meta_Field_Settings::FIELD_DYNAMIC_POST_GROUP ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_group_choices( true );

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Meta_Field_Settings::getAcfFieldName( Meta_Field_Settings::FIELD_DYNAMIC_POST_FIELD ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_field_choices( true );

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Meta_Field_Settings::getAcfFieldName( Meta_Field_Settings::FIELD_DYNAMIC_SOURCE ),
			function ( array $field ) {
				$field['choices'] = array(
					''                                     => __( 'Select', 'acf-views' ),
					Meta_Field_Settings::DYNAMIC_VALUE_POST => __( 'Current post ID', 'acf-views' ),
					Meta_Field_Settings::DYNAMIC_VALUE_POST_FIELD => __( 'Current post field', 'acf-views' ),
					Meta_Field_Settings::DYNAMIC_VALUE_NOW => __( 'Current date/time', 'acf-views' ),
					Meta_Field_Settings::DYNAMIC_VALUE_QUERY => __( 'URL parameter', 'acf-views' ),
					Meta_Field_Settings::DYNAMIC_VALUE_CUSTOM_ARGUMENT => __( 'Shortcode argument', 'acf-views' ),
				);

				return $field;
			}
		);
	}
}
