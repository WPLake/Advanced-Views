<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind;

use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;

defined( 'ABSPATH' ) || exit;

final class Tailwind_Globals_Reader {
	private File_System $file_system;

	public function __construct( File_System $file_system ) {
		$this->file_system = $file_system;
	}

	public function get_global_styles(): string {
		$globals_file = $this->find_globals_file();
		$is_found     = strlen( $globals_file ) > 0;

		if ( $is_found ) {
			$wp_filesystem = $this->file_system->get_wp_filesystem();

			return (string) $wp_filesystem->get_contents( $globals_file );
		}

		return '';
	}

	protected function find_globals_file(): string {
		if ( $this->file_system->is_active() ) {
			$base_folder           = $this->file_system->get_target_base_folder();
			$tailwind_globals_file = sprintf( '%s/tailwind.css', $base_folder );
			$wp_filesystem         = $this->file_system->get_wp_filesystem();

			if ( $wp_filesystem->exists( $tailwind_globals_file ) ) {
				return $tailwind_globals_file;
			}
		}

		return '';
	}
}
