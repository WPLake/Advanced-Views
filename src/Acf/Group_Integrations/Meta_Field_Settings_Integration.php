<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Acf\Groups\Meta_Field_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Plugin;

defined( 'ABSPATH' ) || exit;

class Meta_Field_Settings_Integration extends Acf_Integration {
	private Field_Provider_Cluster $provider_cluster;
	private Plugin $plugin;

	public function __construct( string $target_cpt_name, Field_Provider_Cluster $provider_cluster, Plugin $plugin ) {
		parent::__construct( $target_cpt_name );

		$this->provider_cluster = $provider_cluster;
		$this->plugin           = $plugin;
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_value_type_choices(): array {
		$dynamic_label = __( 'Dynamic value', 'acf-views' );
		$dynamic_label = $this->plugin->get_pro_field_label( $dynamic_label );

		return array(
			Meta_Field_Settings::VALUE_TYPE_LITERAL => __( 'Static value', 'acf-views' ),
			Meta_Field_Settings::VALUE_TYPE_DYNAMIC => $dynamic_label,
		);
	}

	/**
	 * @return array<string,string>
	 */
	protected static function get_dynamic_source_choices(): array {
		return array(
			''                                            => __( 'Select', 'acf-views' ),
			Meta_Field_Settings::DYNAMIC_VALUE_POST       => __( 'Current post ID', 'acf-views' ),
			Meta_Field_Settings::DYNAMIC_VALUE_POST_FIELD => __( 'Current post field', 'acf-views' ),
			Meta_Field_Settings::DYNAMIC_VALUE_NOW        => __( 'Current date/time', 'acf-views' ),
			Meta_Field_Settings::DYNAMIC_VALUE_QUERY      => __( 'URL parameter', 'acf-views' ),
			Meta_Field_Settings::DYNAMIC_VALUE_CUSTOM_ARGUMENT => __( 'Shortcode argument', 'acf-views' ),
		);
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Meta_Field_Settings::FIELD_GROUP              =>
				fn() => $this->provider_cluster->get_group_choices( true ),
			Meta_Field_Settings::FIELD_FIELD_KEY          =>
				fn() => $this->provider_cluster->get_field_choices( true ),
			Meta_Field_Settings::FIELD_VALUE_TYPE         =>
				fn() => $this->get_value_type_choices(),
			Meta_Field_Settings::FIELD_DYNAMIC_POST_GROUP =>
				fn() => $this->provider_cluster->get_group_choices( true ),
			Meta_Field_Settings::FIELD_DYNAMIC_POST_FIELD =>
				fn() => $this->provider_cluster->get_field_choices( true ),
			Meta_Field_Settings::FIELD_DYNAMIC_SOURCE     =>
				fn() => self::get_dynamic_source_choices(),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		Acf_Utils::bind_field_choices( Meta_Field_Settings::class, $choices_callbacks );
	}
}
