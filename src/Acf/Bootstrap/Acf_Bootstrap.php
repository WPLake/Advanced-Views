<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Acf_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookable_factories(): array {
		return array(
			Acf_Groups_Loader::class => fn() => $this->create_groups_loader(),
		);
	}

	protected function create_groups_loader(): Acf_Groups_Loader {
		$plugin        = $this->resolve( Plugin::class );
		$groups_path   = $plugin->get_plugin_path( 'src/Acf/Groups' );
		$namespace_map = array( 'Org\Wplake\Advanced_Views\Acf\Groups' => $groups_path );
		$cpt_names     = array(
			$this->resolve( Layouts_Cpt::class )->cpt_name(),
			$this->resolve( Selections_Cpt::class )->cpt_name(),
		);

		return new Acf_Groups_Loader( $namespace_map, $cpt_names );
	}
}
