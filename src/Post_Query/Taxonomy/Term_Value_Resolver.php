<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Taxonomy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Tax_Field_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Context_Container_Base;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context_Container;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\any;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

class Term_Value_Resolver implements Query_Context_Container {
	use Context_Container_Base;

	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	/**
	 * @return mixed[]
	 */
	public function resolve_term_value( Tax_Field_Settings $term ): array {
		// dynamicTerm is actually a Pro option, but we support them all
		// - as backward compatibility with existing Lite setups.
		if ( $term->is_dynamic_term() ) {
			$resolvers = $this->get_value_resolvers( $term );

			$resolver = $resolvers[ $term->dynamic_term ] ?? null;

			return is_callable( $resolver ) ?
				$resolver() :
				array();
		}

		$term_id = $term->get_term_id();

		return array( $term_id );
	}

	/**
	 * @return array<string, callable(): mixed[]>
	 */
	protected function get_value_resolvers( Tax_Field_Settings $term ): array {
		return array(
			'$current$'         => fn() => array( self::resolve_current_term_id() ),
			'$meta$'            => fn() => $this->resolve_meta_value( $term ),
			'$custom-argument$' => fn() => $this->resolve_custom_value( $term ),
		);
	}

	/**
	 * @return mixed[]
	 */
	protected function resolve_meta_value( Tax_Field_Settings $term ): array {
		$vendor_name = $term->get_vendor_name();
		$field_id    = $term->get_field_id();
		$field_data  = $this->provider_cluster->get_field_meta( $vendor_name, $field_id );

		if ( $field_data->is_field_exist() ) {
			$field_name      = $field_data->get_name();
			$current_term_id = self::resolve_current_term_id();

			return self::get_meta_ids( $field_name, $current_term_id );
		}

		return array();
	}

	/**
	 * @return mixed[]
	 */
	protected function resolve_custom_value( Tax_Field_Settings $term ): array {
		$custom_arguments = $this->get_context()->get_custom_arguments();
		$value            = any( $custom_arguments, $term->custom_argument_name );

		if ( is_numeric( $value ) ) {
			$term_id = int( $value );

			return array( $term_id );
		}

		if ( is_array( $value ) ) {
			return $value;
		}

		return array();
	}

	/**
	 * @return int[]
	 */
	protected static function get_meta_ids( string $field_name, int $object_id ): array {
		$meta_value = get_post_meta( $object_id, $field_name, true );

		if ( is_numeric( $meta_value ) ) {
			$meta_id = int( $meta_value );

			return array( $meta_id );
		}

		if ( is_array( $meta_value ) &&
			key_exists( 0, $meta_value ) &&
			is_numeric( $meta_value[0] ) ) {
			return array_map(
				fn( $item ) => int( $item ),
				$meta_value
			);
		}

		return array();
	}

	protected static function resolve_current_term_id(): int {
		return get_queried_object_id();
	}
}
