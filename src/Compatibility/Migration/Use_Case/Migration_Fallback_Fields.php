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
				fn() => $this->migrate_items()
			);
	}

	protected function migrate_items(): void {
		$file_system = $this->cpt_settings_storage->get_file_system();

		if ( $file_system->is_active() ) {
			foreach ( $this->get_all_with_fallback_fields() as $cpt_settings ) {
				$this->migrate_item( $file_system, $cpt_settings );
			}
		}
	}

	/**
	 * @return Cpt_Settings[] the loaded items only
	 */
	protected function get_all_with_fallback_fields(): array {
		// the items may be cached without the fallback fields, so reload them.
		$this->cpt_settings_storage
			->reset_cache()
			->use_fallback_fields();

		try {
			$all_settings = $this->cpt_settings_storage->get_all();

			return array_filter(
				$all_settings,
				fn( Cpt_Settings $cpt_settings ) => $cpt_settings->isLoaded()
			);
		} finally {
			$this->cpt_settings_storage
				->use_fallback_fields( false )
				->reset_cache();
		}
	}

	/**
	 * The loaded settings already contain the file values (where the JSON misses them), so it's enough to save.
	 */
	protected function migrate_item( File_System $file_system, Cpt_Settings $cpt_settings ): void {
		$short_unique_id = $cpt_settings->get_unique_id( true );
		$fallback_files  = self::find_fallback_files( $file_system, $short_unique_id );

		if ( array() !== $fallback_files ) {
			$this->cpt_settings_storage->save( $cpt_settings );

			$this->delete_saved_files( $file_system, $cpt_settings, $fallback_files );
		}
	}

	/**
	 * @param string[] $fallback_files
	 */
	protected function delete_saved_files( File_System $file_system, Cpt_Settings $cpt_settings, array $fallback_files ): void {
		$short_unique_id = $cpt_settings->get_unique_id( true );
		$saved_files     = self::filter_saved_files( $file_system, $cpt_settings, $fallback_files );
		$unsaved_files   = array_values( array_diff( $fallback_files, $saved_files ) );

		$file_system->delete_fields_from_fs( $short_unique_id, $saved_files );

		if ( array() !== $unsaved_files ) {
			$this->logger->warning(
				'fallback files are not moved into the JSON, their values are ignored until moved manually',
				array(
					'unique_id' => $cpt_settings->get_unique_id(),
					'files'     => $unsaved_files,
				)
			);
		}
	}

	/**
	 * @return string[] the old-format files the item has
	 */
	protected static function find_fallback_files( File_System $file_system, string $short_unique_id ): array {
		$fallback_files = array_keys( Fs_Fields::get_fallback_field_names() );
		$file_values    = $file_system->read_fields_from_fs( $short_unique_id, $fallback_files );

		return array_keys( $file_values );
	}

	/**
	 * The FS writing fails silently (e.g. not writable files), so the JSON is re-read to be sure the values are there.
	 *
	 * @param string[] $fallback_files
	 *
	 * @return string[]
	 */
	protected static function filter_saved_files( File_System $file_system, Cpt_Settings $cpt_settings, array $fallback_files ): array {
		$data_json   = self::read_data_json( $file_system, $cpt_settings->get_unique_id( true ) );
		$field_names = array_intersect_key(
			Fs_Fields::get_fallback_field_names(),
			array_flip( $fallback_files )
		);

		$is_saved = function ( string $field_name ) use ( $cpt_settings, $data_json ): bool {
			$json_key = $cpt_settings::getAcfFieldName( $field_name );
			// @phpstan-ignore-next-line
			$is_empty = '' === $cpt_settings->{$field_name};

			// an empty value isn't stored in the JSON (defaults are skipped), so there is nothing to lose.
			return $is_empty || key_exists( $json_key, $data_json );
		};

		$saved_field_names = array_filter( $field_names, $is_saved );

		return array_keys( $saved_field_names );
	}

	/**
	 * @return array<string,mixed>
	 */
	protected static function read_data_json( File_System $file_system, string $short_unique_id ): array {
		$file_values = $file_system->read_fields_from_fs( $short_unique_id, array( Fs_Fields::DATA_JSON ) );
		$data_json   = $file_values[ Fs_Fields::DATA_JSON ] ?? array();

		return is_array( $data_json ) ?
			$data_json :
			array();
	}
}
