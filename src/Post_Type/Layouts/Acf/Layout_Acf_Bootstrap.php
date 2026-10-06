<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Item_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Layout_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

class Layout_Acf_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookables( Route_Detector $route_detector ): array {
		$integration_classes = $this->get_integration_classes();
		$integrations        = array_map(
			fn( string $class_name ): Hookable => $this->resolve( $class_name ),
			$integration_classes
		);

		$loaders     = $this->get_groups_loaders( $route_detector );
		$mount_point = $this->create_mount_point_integration();

		return array_merge( $loaders, $integrations, array( $mount_point ) );
	}

	/**
	 * @return array<class-string<Hookable>>
	 */
	protected function get_integration_classes(): array {
		return array(
			Layout_Settings_Integration::class,
			Field_Settings_Integration::class,
			Item_Settings_Integration::class,
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function get_groups_loaders( Route_Detector $route_detector ): array {
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$cpt_name    = $layouts_cpt->cpt_name();

		if ( wp_doing_ajax() || $route_detector->is_cpt_admin_route( $cpt_name ) ) {
			$plugin        = $this->resolve( Plugin::class );
			$groups_path   = $plugin->get_plugin_path( 'src/Post_Type/Layouts/Acf/Groups' );
			$namespace_map = array( 'Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups' => $groups_path );

			return array(
				new Acf_Groups_Loader( $namespace_map ),
			);
		}

		return array();
	}

	protected function create_mount_point_integration(): Mount_Point_Settings_Integration {
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$cpt_name    = $layouts_cpt->cpt_name();

		return new Mount_Point_Settings_Integration( $cpt_name );
	}
}
