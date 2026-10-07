<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

class Layout_Acf_Factory extends Container_Facade {
	public function groups_loader(): Acf_Groups_Loader {
		$plugin        = $this->resolve( Plugin::class );
		$groups_path   = $plugin->get_plugin_path( 'src/Post_Type/Layouts/Acf/Groups' );
		$namespace_map = array( 'Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups' => $groups_path );

		return new Acf_Groups_Loader( $namespace_map );
	}

	public function mount_point_integration(): Mount_Point_Settings_Integration {
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$cpt_name    = $layouts_cpt->cpt_name();

		return new Mount_Point_Settings_Integration( $cpt_name );
	}
}
