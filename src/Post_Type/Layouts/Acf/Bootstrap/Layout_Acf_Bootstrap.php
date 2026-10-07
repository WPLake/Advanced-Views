<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Item_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Layout_Settings_Integration;

class Layout_Acf_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookables( Route_Detector $route_detector ): array {
		$integration_classes = $this->get_integration_classes();
		$integrations        = array_map(
			fn( string $class_name ): Hookable => $this->resolve( $class_name ),
			$integration_classes
		);

		$factory = $this->resolve( Layout_Acf_Factory::class );
		$loaders      = $factory->create_groups_loaders( $route_detector );
		$mount_point  = $factory->create_mount_point_integration();

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
}
