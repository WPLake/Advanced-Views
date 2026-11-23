<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Git_Repository;
use Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select\Ajax_Select_Cluster;
use Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select\Cpt_Ajax_Select_Source;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

final class Acf_Factory extends Factory_Base {
	public function groups_loader(): Acf_Groups_Loader {
		$plugin_meta   = $this->resolve( Plugin_Meta::class );
		$groups_path   = $plugin_meta->get_root_path( 'src/Acf/Groups' );
		$namespace_map = array( 'Org\Wplake\Advanced_Views\Acf\Groups' => $groups_path );
		$layout_cpt    = $this->resolve( Layout_Cpt::class );
		$selection_cpt = $this->resolve( Selection_Cpt::class );
		$cpt_names     = array(
			$layout_cpt->cpt_name(),
			$selection_cpt->cpt_name(),
		);

		return new Acf_Groups_Loader( $namespace_map, $cpt_names );
	}

	public function ajax_select_cluster(): Ajax_Select_Cluster {
		$layouts_storage    = $this->resolve( Layout_Settings_Storage::class );
		$layout_cpt         = $this->resolve( Layout_Cpt::class );
		$selections_storage = $this->resolve( Selection_Settings_Storage::class );
		$selection_cpt      = $this->resolve( Selection_Cpt::class );

		$layout_source    = new Cpt_Ajax_Select_Source( $layouts_storage, $layout_cpt );
		$selection_source = new Cpt_Ajax_Select_Source( $selections_storage, $selection_cpt );

		return ( new Ajax_Select_Cluster() )
			->register( Ajax_Select_Cluster::SOURCE_LAYOUTS, $layout_source )
			->register( Ajax_Select_Cluster::SOURCE_POST_SELECTIONS, $selection_source );
	}

	public function git_repository(): Git_Repository {
		$group_creator = $this->resolve( Creator::class );

		return $group_creator->create( Git_Repository::class );
	}
}
