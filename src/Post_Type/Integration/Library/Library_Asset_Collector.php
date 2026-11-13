<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Integration\Library;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;

defined( 'ABSPATH' ) || exit;

final class Library_Asset_Collector {
	private Library_Container $library_container;

	public function __construct( Library_Container $library_container ) {
		$this->library_container = $library_container;
	}

	public function activate_libraries( Cpt_Settings $cpt_settings ): void {
		$libraries = $this->library_container->get_all();

		foreach ( $libraries as $library ) {
			if ( $library instanceof Layout_Library &&
				$cpt_settings instanceof Layout_Settings ) {
				$library->maybe_activate_layout( $cpt_settings );
			}

			if ( $library instanceof Selection_Library &&
				$cpt_settings instanceof Post_Selection_Settings ) {
				$library->maybe_activate_selection( $cpt_settings );
			}
		}
	}
}
