<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Migration\Use_Case;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Migration_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Fs_Fields;

/**
 * Moves the old-format files (the fallback FS fields, e.g. script.js) into the JSON and removes them.
 */
final class Migration_Fallback_Fields extends Migration_Base {
	private Cpt_Settings_Storage $cpt_settings_storage;

	public function __construct( Logger $logger, Cpt_Settings_Storage $cpt_settings_storage ) {
		parent::__construct( $logger );

		$this->cpt_settings_storage = $cpt_settings_storage;
	}

	public function migrate(): void {
		File_System_Loader::instance()
			->add_loaded_callback(
				fn() => $this->move_fallback_fields()
			);
	}

	protected function move_fallback_fields(): void {
		$file_system = $this->cpt_settings_storage->get_file_system();

		if ( $file_system->is_active() ) {
			$this->move_files_into_json();
		}
	}

	protected function move_files_into_json(): void {
		$fs_fields  = $this->cpt_settings_storage->get_fs_fields();
		$file_names = $fs_fields->get_fallback_fs_field_file_names();

		$this->cpt_settings_storage->enable_fallback_fields();

		try {
			$all_settings = $this->cpt_settings_storage->get_all();

			foreach ( $all_settings as $cpt_settings ) {
				$this->move_item_files( $cpt_settings, $file_names );
			}
		} finally {
			$this->cpt_settings_storage->enable_fallback_fields( false );
		}
	}

	/**
	 * The files are removed only if their values are stored in the settings: the item may be cached without the fallback.
	 *
	 * @param string[] $file_names
	 */
	protected function move_item_files( Cpt_Settings $cpt_settings, array $file_names ): void {
		$file_system     = $this->cpt_settings_storage->get_file_system();
		$short_unique_id = $cpt_settings->get_unique_id( true );
		$old_values      = $file_system->read_fields_from_fs( $short_unique_id, $file_names );

		if ( $cpt_settings->isLoaded() && array() !== $old_values ) {
			$stored_files = $this->fill_empty_fields( $cpt_settings, $old_values );

			if ( array() !== $stored_files ) {
				$this->cpt_settings_storage->save( $cpt_settings );

				$persisted_files = $this->get_persisted_files( $file_system, $short_unique_id, $stored_files );
				$file_system->delete_fields_from_fs( $short_unique_id, $persisted_files );
			}
		}
	}

	/**
	 * The FS writing fails silently (e.g. not writable files), so the JSON is re-read to be sure the values are there.
	 *
	 * @param string[] $stored_files
	 *
	 * @return string[]
	 */
	protected function get_persisted_files( File_System $file_system, string $short_unique_id, array $stored_files ): array {
		$saved_values = $file_system->read_fields_from_fs( $short_unique_id, array( 'data.json' ) );
		$saved_json   = $saved_values['data.json'] ?? array();
		$saved_json   = is_array( $saved_json ) ?
			$saved_json :
			array();
		$field_names  = Fs_Fields::get_fallback_field_names();

		return array_values(
			array_filter(
				$stored_files,
				fn( string $field_file ) => key_exists( $field_names[ $field_file ] ?? '', $saved_json )
			)
		);
	}

	/**
	 * @param array<string,mixed> $file_values
	 *
	 * @return string[] files, whose value is now in the settings, so they're safe to remove
	 */
	protected function fill_empty_fields( Cpt_Settings $cpt_settings, array $file_values ): array {
		$stored_files = array();

		foreach ( Fs_Fields::get_fallback_field_names() as $field_file => $field_name ) {
			$file_value = $file_values[ $field_file ] ?? null;

			if ( ! is_string( $file_value ) ) {
				continue;
			}

			// @phpstan-ignore-next-line
			$current_value = $cpt_settings->{$field_name};
			$is_empty      = '' === $current_value;
			$is_stored     = $is_empty || $file_value === $current_value;

			if ( $is_empty ) {
				// @phpstan-ignore-next-line
				$cpt_settings->{$field_name} = $file_value;
			}

			if ( $is_stored ) {
				$stored_files[] = $field_file;
			}
		}

		return $stored_files;
	}
}
