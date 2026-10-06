<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations;

use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Acf_Integration;
use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

defined( 'ABSPATH' ) || exit;

class Layout_Settings_Integration extends Acf_Integration {
	private Field_Provider_Cluster $provider_cluster;
	private Engines_Storage $engines_storage;

	public function __construct(
		Layouts_Cpt $layout_cpt,
		Field_Provider_Cluster $provider_cluster,
		Engines_Storage $engines_storage
	) {
		parent::__construct( $layout_cpt->cpt_name() );

		$this->provider_cluster = $provider_cluster;
		$this->engines_storage  = $engines_storage;
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_parent_field_choices(): array {
		return $this->provider_cluster->get_field_choices( true, true );
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Layout_Settings::FIELD_GROUP           =>
				fn() => $this->provider_cluster->get_group_choices(),
			Layout_Settings::FIELD_PARENT_FIELD    =>
				fn() => $this->get_parent_field_choices(),
			Layout_Settings::FIELD_TEMPLATE_ENGINE =>
				fn() => $this->engines_storage->get_choices(),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		Acf_Utils::bind_field_choices( Layout_Settings::class, $choices_callbacks );
	}
}
