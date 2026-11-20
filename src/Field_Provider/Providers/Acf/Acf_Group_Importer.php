<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf;

use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringOrNull;

defined( 'ABSPATH' ) || exit;

final class Acf_Group_Importer {
	private Logger $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @return mixed[]|null
	 */
	public function export_group( string $group_id ): ?array {
		$is_supported = function_exists( 'acf_get_field_group' ) &&
						function_exists( 'acf_get_fields' ) &&
						function_exists( 'acf_prepare_internal_post_type_for_export' );

		$group_info = $is_supported ?
			$this->get_group_with_fields( $group_id ) :
			null;

		if ( is_array( $group_info ) ) {
			return $this->prepare_group_info( $group_info, $group_id );
		}

		return null;
	}

	/**
	 * @param mixed[] $group_data
	 */
	public function import_group( array $group_data ): ?string {
		$is_supported = function_exists( 'acf_get_field_group' ) &&
						function_exists( 'acf_import_field_group' );

		if ( $is_supported ) {
			return $this->import_supported_group( $group_data );
		}

		return null;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	protected function get_group_with_fields( string $group_id ): ?array {
		$group_info = $this->find_group_info( $group_id );

		if ( is_array( $group_info ) ) {
			return $this->add_fields_to_group_info( $group_info, $group_id );
		}

		return null;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	protected function find_group_info( string $group_id ): ?array {
		$group_info = function_exists( 'acf_get_field_group' ) ?
			acf_get_field_group( $group_id ) :
			false;

		if ( is_array( $group_info ) ) {
			return $group_info;
		}

		$log_context = array(
			'group_id' => $group_id,
		);

		$this->logger->warning( 'failed to get ACF field group info', $log_context );

		return null;
	}

	/**
	 * @param array<string,mixed> $group_info
	 *
	 * @return array<string,mixed>|null
	 */
	protected function add_fields_to_group_info( array $group_info, string $group_id ): ?array {
		// fields are missing (empty) in the 'acf_get_field_group' call.
		$fields_info = function_exists( 'acf_get_fields' ) ?
			acf_get_fields( $group_info ) :
			false;

		if ( is_array( $fields_info ) ) {
			$group_info['fields'] = $fields_info;

			return $group_info;
		}

		$log_context = array(
			'group_id' => $group_id,
		);

		$this->logger->warning( 'failed to get ACF fields info', $log_context );

		return null;
	}

	/**
	 * @param array<string,mixed> $group_info
	 *
	 * @return mixed[]|null
	 */
	protected function prepare_group_info( array $group_info, string $group_id ): ?array {
		$prepared_info = function_exists( 'acf_prepare_internal_post_type_for_export' ) ?
			acf_prepare_internal_post_type_for_export( $group_info ) :
			false;

		if ( is_array( $prepared_info ) ) {
			return $prepared_info;
		}

		$log_context = array(
			'group_id' => $group_id,
		);

		$this->logger->warning( 'failed to prepare ACF field group for export', $log_context );

		return null;
	}

	/**
	 * @param mixed[] $group_data
	 */
	protected function import_supported_group( array $group_data ): ?string {
		$group_data = $this->with_existing_group_id( $group_data );
		$imported   = function_exists( 'acf_import_field_group' ) ?
			acf_import_field_group( $group_data ) :
			false;

		if ( is_array( $imported ) ) {
			return stringOrNull( $imported, 'key' );
		}

		$log_context = array(
			'group_data' => $imported,
		);

		$this->logger->warning( 'failed to import ACF field group', $log_context );

		return null;
	}

	/**
	 * @param mixed[] $group_data
	 *
	 * @return mixed[]
	 */
	protected function with_existing_group_id( array $group_data ): array {
		$group_key           = string( $group_data, 'key' );
		$existing_group_info = function_exists( 'acf_get_field_group' ) ?
			acf_get_field_group( $group_key ) :
			false;

		if ( is_array( $existing_group_info ) ) {
			$group_id         = int( $existing_group_info, 'ID' );
			$group_data['ID'] = $group_id;

			$log_context = array(
				'key' => $group_key,
				'ID'  => $group_id,
			);

			$this->logger->debug( 'ACF group to import already exists: overriding instead of creation', $log_context );
		}

		return $group_data;
	}
}
