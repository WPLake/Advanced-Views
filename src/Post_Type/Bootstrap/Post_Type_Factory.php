<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Options_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Post_Type_Factory extends Factory_Base {
	public function git_lab_api(): Git_Lab_Api {
		return new Git_Lab_Api(
			$this->resolve( Logger::class ),
			$this->resolve( Options_Storage::class ),
			array(
				$this->resolve( Layouts_Cpt::class ),
				$this->resolve( Selections_Cpt::class ),
			)
		);
	}

	public function point_mounter(): Point_Mounter {
		return new Point_Mounter(
			array(
				new Point_Provider(
					$this->resolve( Layout_Settings_Storage::class ),
					$this->resolve( Layouts_Cpt::class )
				),
				new Point_Provider(
					$this->resolve( Selection_Settings_Storage::class ),
					$this->resolve( Selections_Cpt::class )
				),
			)
		);
	}
}
