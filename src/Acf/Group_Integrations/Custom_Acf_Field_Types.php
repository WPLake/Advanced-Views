<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;

class Custom_Acf_Field_Types extends Hookable_Base implements Hookable {

	private Layout_Settings_Storage $layouts_settings_storage;

	public function __construct( Layout_Settings_Storage $layouts_settings_storage ) {
		$this->layouts_settings_storage = $layouts_settings_storage;
	}

	public function register_av_slug_select_field(): void {
		if ( false === function_exists( 'acf_register_field_type' ) ) {
			return;
		}

		acf_register_field_type( new Av_Slug_Select_Field( $this->layouts_settings_storage ) );
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		// must be present on both edit screens and during ajax requests.
		return $route_detector->is_admin_route() &&
			( $route_detector->is_cpt_admin_route( Hard_Layout_Cpt::cpt_name(), Route_Detector::CPT_EDIT ) ||
				$route_detector->is_cpt_admin_route( Hard_Post_Selection_Cpt::cpt_name(), Route_Detector::CPT_EDIT ) ||
				wp_doing_ajax() );
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action(
			'acf/include_field_types',
			array( $this, 'register_av_slug_select_field' )
		);
	}
}
