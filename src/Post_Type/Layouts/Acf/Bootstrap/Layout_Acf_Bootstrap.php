<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Item_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations\Layout_Settings_Integration;

class Layout_Acf_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookable_classes(): array {
		return array(
			Layout_Settings_Integration::class,
			Field_Settings_Integration::class,
			Item_Settings_Integration::class,
		);
	}

	public function get_hookable_factories(): array {
		$factory = $this->resolve( Layout_Acf_Factory::class );

		return array(
			Acf_Groups_Loader::class                => fn() => $factory->groups_loader(),
			Mount_Point_Settings_Integration::class => fn() => $factory->mount_point_integration(),
		);
	}
}
