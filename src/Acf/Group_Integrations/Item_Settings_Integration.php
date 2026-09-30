<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;

defined( 'ABSPATH' ) || exit;

class Item_Settings_Integration extends Acf_Integration {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( string $target_cpt_name, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $target_cpt_name );

		$this->provider_cluster = $provider_cluster;
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Item_Settings::FIELD_GROUP =>
				fn() => $this->provider_cluster->get_group_choices(),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		Acf_Utils::bind_field_choices( Item_Settings::class, $choices_callbacks );
	}
}
