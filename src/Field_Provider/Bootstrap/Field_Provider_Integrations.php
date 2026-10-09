<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;

final class Field_Provider_Integrations implements Actor {
	private Field_Provider_Cluster $provider_cluster;
	private Item_Settings $item_settings;
	private Layout_Settings_Storage $layouts_settings_storage;
	private Layout_Save_Actions $layouts_cpt_save_actions;
	private Layout_Factory $layout_factory;
	private Repeater_Field_Settings $repeater_field_settings;
	private Layout_Shortcode $layout_shortcode;
	private Settings_Storage $settings;
	private Plugin_Cpt $plugin_cpt;

	public function __construct(
		Field_Provider_Cluster $provider_cluster,
		Item_Settings $item_settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Layout_Save_Actions $layouts_cpt_save_actions,
		Layout_Factory $layout_factory,
		Repeater_Field_Settings $repeater_field_settings,
		Layout_Shortcode $layout_shortcode,
		Settings_Storage $settings,
		Plugin_Cpt $plugin_cpt
	) {
		$this->provider_cluster         = $provider_cluster;
		$this->item_settings            = $item_settings;
		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->layouts_cpt_save_actions = $layouts_cpt_save_actions;
		$this->layout_factory           = $layout_factory;
		$this->repeater_field_settings  = $repeater_field_settings;
		$this->layout_shortcode         = $layout_shortcode;
		$this->settings                 = $settings;
		$this->plugin_cpt               = $plugin_cpt;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return true;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		$this->provider_cluster->make_integration_instances(
			$route_detector,
			$this->item_settings,
			$this->layouts_settings_storage,
			$this->layouts_cpt_save_actions,
			$this->layout_factory,
			$this->repeater_field_settings,
			$this->layout_shortcode,
			$this->settings,
			$this->plugin_cpt
		);
	}
}
