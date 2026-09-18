<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Template_Engine\Engines\Engines_Storage;

defined( 'ABSPATH' ) || exit;

class Layout_Settings_Integration extends Acf_Integration {
	private Field_Provider_Cluster $provider_cluster;
	private Engines_Storage $engines_storage;

	public function __construct(
		string $target_cpt_name,
		Field_Provider_Cluster $provider_cluster,
		Engines_Storage $engines_storage
	) {
		parent::__construct( $target_cpt_name );

		$this->provider_cluster = $provider_cluster;
		$this->engines_storage  = $engines_storage;
	}

	protected function set_field_choices(): void {
		self::add_filter(
			'acf/load_field/name=' . Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_GROUP ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_group_choices();

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_PARENT_FIELD ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_field_choices(
					true,
					true
				);

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_TEMPLATE_ENGINE ),
			function ( array $field ) {
				$field['choices'] = $this->engines_storage->get_choices();

				return $field;
			}
		);
	}
}
