<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Integrations;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Acf\Integration\Groups\Acf_Integration_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;

defined( 'ABSPATH' ) || exit;

final class Layout_Settings_Integration extends Acf_Integration_Base {
	private Field_Provider_Cluster $provider_cluster;
	private Engines_Storage $engines_storage;

	public function __construct(
		Layout_Cpt $layout_cpt,
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
	 * @return array<string,callable(): array<int|string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Layout_Settings::FIELD_GROUP                => fn() => $this->provider_cluster->get_group_choices(),
			Layout_Settings::FIELD_PARENT_FIELD         => fn() => $this->get_parent_field_choices(),
			Layout_Settings::FIELD_TEMPLATE_ENGINE      => fn() => $this->engines_storage->get_choices(),
			Layout_Settings::FIELD_SOURCE_MENUS         => fn() => self::get_menu_choices(),
			Layout_Settings::FIELD_SOURCE_ROLES_WITH    => fn() => self::get_role_choices(),
			Layout_Settings::FIELD_SOURCE_ROLES_WITHOUT => fn() => self::get_role_choices(),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		Acf_Utils::bind_field_choices( Layout_Settings::class, $choices_callbacks );
	}

	/**
	 * @return array<int|string,string>
	 */
	protected static function get_menu_choices(): array {
		$choices = array();

		$menus = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$choices[ (string) $menu->term_id ] = $menu->name;
		}

		return $choices;
	}

	/**
	 * @return array<string,string>
	 */
	protected static function get_role_choices(): array {
		$role_names = wp_roles()->get_names();

		return array_map( 'translate_user_role', $role_names );
	}
}
