<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Bootstrap\Acf_Groups_Loader;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Selection_Acf_Factory extends Container_Facade {
	public function groups_loader(): Acf_Groups_Loader {
		$plugin        = $this->resolve( Plugin::class );
		$groups_path   = $plugin->get_plugin_path( 'src/Post_Type/Post_Selections/Acf/Groups' );
		$namespace_map = array( 'Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups' => $groups_path );
		$cpt_name      = $this->resolve( Selections_Cpt::class )->cpt_name();

		return new Acf_Groups_Loader( $namespace_map, array( $cpt_name ) );
	}

	public function mount_point_integration(): Mount_Point_Settings_Integration {
		$selections_cpt = $this->resolve( Selections_Cpt::class );
		$cpt_name       = $selections_cpt->cpt_name();

		return new Mount_Point_Settings_Integration( $cpt_name );
	}
}
