<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

defined( 'ABSPATH' ) || exit;

final class Related_Groups_Import_Result {
	/**
	 * @var array<string, string[]> vendorName => groupIds (like group_x, not wp post ids)
	 */
	private array $group_ids;

	public function __construct() {
		$this->group_ids = array();
	}

	public function add_group( string $vendor_name, string $group_id ): void {
		$this->group_ids[ $vendor_name ] ??= array();
		$this->group_ids[ $vendor_name ][] = $group_id;
	}

	public function merge( Related_Groups_Import_Result $related_groups_import_result ): void {
		foreach ( $related_groups_import_result->group_ids as $vendor_name => $group_ids ) {
			$this->group_ids[ $vendor_name ] ??= array();
			$this->group_ids[ $vendor_name ]   = array_merge( $this->group_ids[ $vendor_name ], $group_ids );
		}
	}

	public function get_query_string_value(): string {
		$vendor_parts = array();

		foreach ( $this->group_ids as $vendor_name => $group_ids ) {
			$group_ids_value = implode( ',', $group_ids );

			$vendor_parts[] = sprintf( '%s:%s', $vendor_name, $group_ids_value );
		}

		return implode( ';', $vendor_parts );
	}

	public function from_query_string( string $query_string ): void {
		$parsed_group_ids = self::parse_query_string( $query_string );

		$this->group_ids = array_replace( $this->group_ids, $parsed_group_ids );
	}

	/**
	 * @return  array<string, string[]> vendorName => groupIds (like group_x, not wp post ids)
	 */
	public function get_group_ids(): array {
		return $this->group_ids;
	}

	/**
	 * @return array<string, string[]>
	 */
	protected static function parse_query_string( string $query_string ): array {
		$group_ids = array();
		$items     = explode( ';', $query_string );

		foreach ( $items as $item ) {
			$parts = explode( ':', $item );

			if ( 2 === count( $parts ) ) {
				$vendor_name = $parts[0];

				$group_ids[ $vendor_name ] = explode( ',', $parts[1] );
			}
		}

		return $group_ids;
	}
}
