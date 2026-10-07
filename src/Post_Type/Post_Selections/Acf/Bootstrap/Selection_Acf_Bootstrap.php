<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Meta_Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Post_Selection_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Tax_Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Selection_Acf_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookable_classes(): array {
		return array(
			Post_Selection_Settings_Integration::class,
			// metaField is a part of the Meta Filter, so we use the selection CPT here.
			Meta_Field_Settings_Integration::class,
			Tax_Field_Settings_Integration::class,
		);
	}

	protected function resolve_hookable_instances( Route_Detector $route_detector ): array {
		$factory     = $this->resolve( Selection_Acf_Factory::class );
		$cpt_name    = $this->resolve( Selections_Cpt::class )->cpt_name();
		$mount_point = $factory->mount_point_integration();

		if ( wp_doing_ajax() || $route_detector->is_cpt_admin_route( $cpt_name ) ) {
			return array( $factory->groups_loader(), $mount_point );
		}

		return array( $mount_point );
	}
}
