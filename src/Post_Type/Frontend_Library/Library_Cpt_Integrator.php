<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Frontend_Library;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;

defined( 'ABSPATH' ) || exit;

class Library_Cpt_Integrator {
	private Library_Code_Builder $code_builder;
	private Code_Marker $code_marker;

	public function __construct( Library_Code_Builder $code_builder, Code_Marker $code_marker ) {
		$this->code_builder = $code_builder;
		$this->code_marker  = $code_marker;
	}

	/**
	 * Writes the libraries' pieces, wrapped by markers, into the Cpt's CSS code, synced with the current code.
	 */
	public function integrate_css_code( Cpt_Settings $cpt_settings ): void {
		$libraries_pieces = $this->code_builder->build_css( $cpt_settings );
		$marked_pieces    = $this->mark_pieces( $libraries_pieces );

		$cpt_settings->css_code = $this->code_marker->sync_code( $cpt_settings->css_code, $marked_pieces );
	}

	/**
	 * Writes the libraries' pieces, wrapped by markers, into the Cpt's JS code, synced with the current code.
	 */
	public function integrate_js_code( Cpt_Settings $cpt_settings ): void {
		$libraries_pieces = $this->code_builder->build_js( $cpt_settings );
		$marked_pieces    = $this->mark_pieces( $libraries_pieces );

		$cpt_settings->js_code = $this->code_marker->sync_code( $cpt_settings->js_code, $marked_pieces );
	}

	/**
	 * @param array<string,array<string,string>> $libraries_pieces auto-discover name => pieces
	 *
	 * @return array<string,string>
	 */
	protected function mark_pieces( array $libraries_pieces ): array {
		$marked_pieces = array();

		foreach ( $libraries_pieces as $auto_discover_name => $pieces ) {
			$library_pieces = $this->code_marker->mark_pieces( $auto_discover_name, $pieces );
			$marked_pieces  = array_merge( $marked_pieces, $library_pieces );
		}

		return $marked_pieces;
	}
}
