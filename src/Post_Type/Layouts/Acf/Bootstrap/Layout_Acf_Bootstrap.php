<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Item_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Layout_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

class Layout_Acf_Bootstrap extends Module_Bootstrap_Base {
	protected function get_hookable_classes(): array {
		return array(
			Layout_Settings_Integration::class,
			Field_Settings_Integration::class,
			Item_Settings_Integration::class,
		);
	}

	protected function resolve_hookable_instances( Route_Detector $route_detector ): array {
		$factory     = $this->resolve( Layout_Acf_Factory::class );
		$cpt_name    = $this->resolve( Layouts_Cpt::class )->cpt_name();
		$mount_point = $factory->mount_point_integration();

		if ( wp_doing_ajax() || $route_detector->is_cpt_admin_route( $cpt_name ) ) {
			return array( $factory->groups_loader(), $mount_point );
		}

		return array( $mount_point );
	}
}
