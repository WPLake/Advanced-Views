<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Acf_Factory extends Factory_Base {
	public function groups_loader(): Acf_Groups_Loader {
		$plugin        = $this->resolve( Plugin::class );
		$groups_path   = $plugin->get_root_path( 'src/Acf/Groups' );
		$namespace_map = array( 'Org\Wplake\Advanced_Views\Acf\Groups' => $groups_path );
		$cpt_names     = array(
			$this->resolve( Layouts_Cpt::class )->cpt_name(),
			$this->resolve( Selections_Cpt::class )->cpt_name(),
		);

		return new Acf_Groups_Loader( $namespace_map, $cpt_names );
	}
}
