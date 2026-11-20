<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;

defined( 'ABSPATH' ) || exit;

final class Related_Groups_Exporter {
	/**
	 * @param array<string, Field_Provider> $providers
	 * @param array<string, string> $import_files name => content
	 */
	public static function import_files( array $providers, array $import_files ): Related_Groups_Import_Result {
		$related_groups_import_result = new Related_Groups_Import_Result();

		foreach ( $import_files as $file_name => $file_content ) {
			$file_import_result = self::import_file( $providers, $file_name, $file_content );

			$related_groups_import_result->merge( $file_import_result );
		}

		return $related_groups_import_result;
	}

	/**
	 * @param array<string, Field_Provider> $providers
	 * @param array<string, string[]> $related_groups vendorName => groupIds (not unique)
	 *
	 * @return array<string, string> fileName => content
	 */
	public static function get_export_files( array $providers, array $related_groups ): array {
		$related_group_export_data  = self::get_groups_export_data( $providers, $related_groups );
		$related_group_export_files = array();

		foreach ( $related_group_export_data as $vendor_name => $groups_export_data ) {
			$export_content = self::create_export_content( $providers[ $vendor_name ], $groups_export_data );

			if ( is_string( $export_content ) ) {
				$file_name = sprintf( '%s.json', $vendor_name );

				$related_group_export_files[ $file_name ] = $export_content;
			}
		}

		return $related_group_export_files;
	}

	/**
	 * @param array<string, Field_Provider> $providers
	 */
	protected static function import_file( array $providers, string $file_name, string $file_content ): Related_Groups_Import_Result {
		$file_vendor = self::get_import_file_vendor( $providers, $file_name );
		$import_data = json_decode( $file_content, true );

		if ( $file_vendor instanceof Field_Provider && is_array( $import_data ) ) {
			return self::import_vendor_groups( $file_vendor, $import_data );
		}

		return new Related_Groups_Import_Result();
	}

	/**
	 * @param array<string, Field_Provider> $providers
	 */
	protected static function get_import_file_vendor( array $providers, string $file_name ): ?Field_Provider {
		$json_position = strpos( $file_name, '.json' );
		$vendor_name   = str_replace( '.json', '', $file_name );

		if ( is_int( $json_position ) && key_exists( $vendor_name, $providers ) ) {
			return $providers[ $vendor_name ];
		}

		return null;
	}

	/**
	 * @param mixed[] $import_data
	 */
	protected static function import_vendor_groups( Field_Provider $vendor, array $import_data ): Related_Groups_Import_Result {
		$import_result = new Related_Groups_Import_Result();
		$meta_data     = arr( $import_data, 'meta' );
		// compatibility with the old export format, which didn't have meta at all.
		$groups_data = arr( $import_data, 'groups' );
		$vendor_name = $vendor->get_name();

		foreach ( $groups_data as $group_data ) {
			$group_data        = arr( $group_data );
			$imported_group_id = $vendor->import_group( $group_data, $meta_data );

			if ( is_string( $imported_group_id ) ) {
				$import_result->add_group( $vendor_name, $imported_group_id );
			}
		}

		return $import_result;
	}

	/**
	 * @param array<string, Field_Provider> $providers
	 * @param array<string, string[]> $related_groups
	 *
	 * @return array<string, mixed[]> vendorName => groups export data
	 */
	protected static function get_groups_export_data( array $providers, array $related_groups ): array {
		$related_group_export_data = array();

		foreach ( $related_groups as $vendor_name => $group_ids ) {
			$vendor    = $providers[ $vendor_name ];
			$group_ids = array_unique( $group_ids );

			foreach ( $group_ids as $group_id ) {
				// feature can be not supported by the vendor (e.g. WP or Woo vendors).
				$group_export_data = $vendor->get_group_export_data( $group_id );

				if ( is_array( $group_export_data ) ) {
					$related_group_export_data[ $vendor_name ] ??= array();
					$related_group_export_data[ $vendor_name ][] = $group_export_data;
				}
			}
		}

		return $related_group_export_data;
	}

	/**
	 * @param mixed[] $groups_export_data
	 */
	protected static function create_export_content( Field_Provider $vendor, array $groups_export_data ): ?string {
		$export_meta_data = $vendor->get_export_meta_data( $groups_export_data );

		$export_data = array(
			'meta'   => $export_meta_data,
			'groups' => $groups_export_data,
		);

		$export_content = wp_json_encode(
			$export_data,
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
		);

		return is_string( $export_content ) ?
			$export_content :
			null;
	}
}
