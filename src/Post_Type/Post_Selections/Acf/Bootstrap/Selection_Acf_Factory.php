<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Selection_Acf_Factory extends Container_Facade {
	/**
	 * @return Hookable[]
	 */
	public function create_groups_loaders( Route_Detector $route_detector ): array {
		$selections_cpt = $this->resolve( Selections_Cpt::class );
		$cpt_name       = $selections_cpt->cpt_name();

		if ( wp_doing_ajax() || $route_detector->is_cpt_admin_route( $cpt_name ) ) {
			$plugin        = $this->resolve( Plugin::class );
			$groups_path   = $plugin->get_plugin_path( 'src/Post_Type/Post_Selections/Acf/Groups' );
			$namespace_map = array( 'Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups' => $groups_path );

			return array(
				new Acf_Groups_Loader( $namespace_map ),
			);
		}

		return array();
	}

	public function create_mount_point_integration(): Mount_Point_Settings_Integration {
		$selections_cpt = $this->resolve( Selections_Cpt::class );
		$cpt_name       = $selections_cpt->cpt_name();

		return new Mount_Point_Settings_Integration( $cpt_name );
	}
}
