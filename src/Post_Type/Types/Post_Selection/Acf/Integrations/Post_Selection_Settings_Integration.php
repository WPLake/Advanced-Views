<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Integrations;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Acf\Integration\Groups\Acf_Integration_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;

defined( 'ABSPATH' ) || exit;

final class Post_Selection_Settings_Integration extends Acf_Integration_Base {
	private Field_Provider_Cluster $provider_cluster;
	private Engines_Storage $engines_storage;

	public function __construct(
		Selection_Cpt $selection_cpt,
		Field_Provider_Cluster $provider_cluster,
		Engines_Storage $engines_storage
	) {
		parent::__construct( $selection_cpt->cpt_name() );

		$this->provider_cluster = $provider_cluster;
		$this->engines_storage  = $engines_storage;
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Post_Selection_Settings::FIELD_ORDER_BY_META_FIELD_GROUP =>
				fn() => $this->provider_cluster->get_group_choices( true ),
			Post_Selection_Settings::FIELD_ORDER_BY_META_FIELD_KEY =>
				fn() => $this->provider_cluster->get_field_choices( true ),
			Post_Selection_Settings::FIELD_POST_TYPES      =>
				fn() => $this->get_post_type_choices(),
			Post_Selection_Settings::FIELD_POST_STATUSES   =>
				fn() => self::get_post_status_choices(),
			Post_Selection_Settings::FIELD_TEMPLATE_ENGINE =>
				fn() => $this->engines_storage->get_choices(),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		Acf_Utils::bind_field_choices( Post_Selection_Settings::class, $choices_callbacks );
	}

	/**
	 * @return string[]
	 */
	protected static function get_post_status_choices(): array {
		return get_post_statuses();
	}
}
