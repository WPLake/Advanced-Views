<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Meta;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Meta_Field_Rule;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Query\Meta\Field\List_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Meta\Field\Meta_Field;
use Org\Wplake\Advanced_Views\Post_Query\Meta\Field\Value_Query_Builder;

class Field_Query_Builder {
	private Field_Provider_Cluster $provider_cluster;
	private Meta_Value_Resolver $value_resolver;

	public function __construct( Field_Provider_Cluster $provider_cluster, Meta_Value_Resolver $meta_value_resolver ) {
		$this->provider_cluster = $provider_cluster;
		$this->value_resolver   = $meta_value_resolver;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function build_field_query( Meta_Field_Rule $field ): array {
		$field_meta = $this->provider_cluster->get_field_meta(
			$this->provider_cluster->get_vendor_name_by_key( $field->get_vendor_key() ),
			$field->get_field_id()
		);

		$is_supported_field = $this->value_resolver->is_supported_field( $field, $field_meta );

		return $is_supported_field ?
			$this->resolve_query_arguments( $field, $field_meta ) :
			array();
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function resolve_query_arguments( Meta_Field_Rule $field, Field_Meta $field_meta ): array {
		$is_list_field = Meta_Field::is_object_field( $field_meta->get_type() ) &&
								// e.g. 'post_object' can be single.
								$field_meta->is_multiple();

		$resolve_value = fn( string $raw_value ) =>
			$this->value_resolver->resolve_meta_value( $raw_value, $field_meta );

		if ( $is_list_field ) {
			$resolved_value = $resolve_value( $field->resolve_value() );

			return ( new List_Query_Builder( $field, $field_meta, $resolved_value ) )
				->build_list_query();
		}

		return ( new Value_Query_Builder( $field, $field_meta, $resolve_value ) )
			->build_value_query();
	}
}
