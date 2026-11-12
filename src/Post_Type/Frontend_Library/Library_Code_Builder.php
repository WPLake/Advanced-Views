<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Frontend_Library;

use Closure;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;

defined( 'ABSPATH' ) || exit;

class Library_Code_Builder {
	private Library_Container $library_container;

	public function __construct( Library_Container $library_container ) {
		$this->library_container = $library_container;
	}

	/**
	 * @return array<string,array<string,string>> the library's auto-discover name => pieces
	 */
	public function build_css( Cpt_Settings $cpt_settings ): array {
		$build_pieces = fn( Frontend_Library $library ) => $this->generate_library_css( $library, $cpt_settings );

		return $this->collect_pieces( $build_pieces );
	}

	/**
	 * @return array<string,array<string,string>> the library's auto-discover name => pieces
	 */
	public function build_js( Cpt_Settings $cpt_settings ): array {
		$build_pieces = fn( Frontend_Library $library ) => $this->generate_library_js( $library, $cpt_settings );

		return $this->collect_pieces( $build_pieces );
	}

	/**
	 * @param Closure(Frontend_Library):array<string,string> $build_pieces
	 *
	 * @return array<string,array<string,string>>
	 */
	protected function collect_pieces( Closure $build_pieces ): array {
		$libraries_pieces = array();

		foreach ( $this->library_container->get_all() as $library ) {
			$libraries_pieces[ $library->get_auto_discover_name() ] = $build_pieces( $library );
		}

		return $libraries_pieces;
	}

	/**
	 * @return array<string,string>
	 */
	protected function generate_library_css( Frontend_Library $library, Cpt_Settings $cpt_settings ): array {
		if ( $library instanceof Layout_Library &&
			$cpt_settings instanceof Layout_Settings ) {
			return $library->generate_layout_css( $cpt_settings );
		}

		if ( $library instanceof Selection_Library &&
			$cpt_settings instanceof Post_Selection_Settings &&
			$library->is_target_selection( $cpt_settings ) ) {
			return $library->generate_selection_css( $cpt_settings );
		}

		return array();
	}

	/**
	 * @return array<string,string>
	 */
	protected function generate_library_js( Frontend_Library $library, Cpt_Settings $cpt_settings ): array {
		if ( $library instanceof Layout_Library &&
			$cpt_settings instanceof Layout_Settings ) {
			return $library->generate_layout_js( $cpt_settings );
		}

		if ( $library instanceof Selection_Library &&
			$cpt_settings instanceof Post_Selection_Settings &&
			$library->is_target_selection( $cpt_settings ) ) {
			return $library->generate_selection_js( $cpt_settings );
		}

		return array();
	}
}
