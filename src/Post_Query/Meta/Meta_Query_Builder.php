<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Meta;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Meta_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Meta_Filter_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Meta_Rule_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Core\Query_Utils;

final class Meta_Query_Builder implements Post_Query_Builder {
	private Field_Query_Builder $field_query_builder;

	public function __construct( Field_Query_Builder $field_query_builder ) {
		$this->field_query_builder = $field_query_builder;
	}

	public function build_post_query( Post_Selection_Settings $selection_settings ): array {
		$meta_filter = $selection_settings->meta_filter;

		$arguments = array(
			// @phpcs:ignore.
			'meta_query' => array(
				'condition' => count( $meta_filter->rules ) > 0,
				'value'     => fn() => $this->get_meta_arguments( $meta_filter ),
			),
		);

		return Query_Utils::filter_arguments( $arguments );
	}

	/**
	 * @return mixed[] | null
	 */
	protected function get_meta_arguments( Meta_Filter_Settings $meta_filter ): ?array {
		$rule_queries = array_map(
			fn( Meta_Rule_Settings $rule ) => $this->get_rule_arguments( $rule ),
			$meta_filter->rules
		);

		$sub_queries = array_filter(
			$rule_queries,
			fn( $group_query ) => is_array( $group_query ),
		);

		if ( count( $sub_queries ) > 0 ) {
			return array_merge(
				array(
					'relation' => $this->get_relation( $meta_filter->relation ),
				),
				$sub_queries
			);
		}

		return null;
	}

	/**
	 * @return mixed[] | null
	 */
	protected function get_rule_arguments( Meta_Rule_Settings $rule ): ?array {
		$field_queries = array_map(
			fn( Meta_Field_Settings $field ) => $this->get_field_arguments( $field ),
			$rule->fields
		);

		$sub_queries = array_filter(
			$field_queries,
			fn( $field_query ) => is_array( $field_query )
		);

		if ( count( $sub_queries ) > 0 ) {
			return array_merge(
				array(
					'relation' => $this->get_relation( $rule->relation ),
				),
				$sub_queries
			);
		}

		return null;
	}

	protected function get_relation( string $custom_relation ): string {
		// can be empty (when hidden).
		return strlen( $custom_relation ) > 0 ?
			$custom_relation :
			'AND';
	}

	/**
	 * @return mixed[] | null
	 */
	protected function get_field_arguments( Meta_Field_Settings $field ): ?array {
		$arguments = $this->field_query_builder->build_field_query( $field );

		if ( count( $arguments ) > 0 ) {
			return $arguments;
		}

		return null;
	}
}
