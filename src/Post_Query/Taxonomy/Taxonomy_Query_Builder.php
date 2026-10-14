<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Taxonomy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Query_Settings;
use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Term_Settings;
use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Tax_Filter;
use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Tax_Rule;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Core\Query_Utils;

final class Taxonomy_Query_Builder implements Post_Query_Builder {
	private Term_Query_Builder $term_query_builder;

	public function __construct( Term_Query_Builder $term_query_builder ) {
		$this->term_query_builder = $term_query_builder;
	}

	public function build_post_query( Query_Settings $selection_settings ): array {
		$filter = $selection_settings->get_tax_filter();

		$arguments = array(
			// @phpcs:ignore.
			'tax_query' => array(
				'condition' => count( $filter->get_rules() ) > 0,
				'value'     => fn () => $this->get_taxonomy_arguments( $filter ),
			),
		);

		return Query_Utils::filter_arguments( $arguments );
	}

	/**
	 * @return mixed[]|null
	 */
	protected function get_taxonomy_arguments( Tax_Filter $filter ): ?array {
		$group_queries = array_map(
			fn ( Tax_Rule $term_group ) => $this->get_group_arguments( $term_group ),
			$filter->get_rules()
		);

		$sub_queries = array_filter(
			$group_queries,
			fn( $group_query ) => is_array( $group_query )
		);

		if ( count( $sub_queries ) > 0 ) {
			$relation  = $this->get_relation( $filter->get_relation() );
			$arguments = array(
				'relation' => $relation,
			);

			return array_merge( $arguments, $sub_queries );
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
	 * @return array<string,mixed>|null
	 */
	protected function get_group_arguments( Tax_Rule $term_group ): ?array {
		$has_terms = count( $term_group->get_terms() ) > 0;

		if ( $has_terms ) {
			$term_queries = array_map(
				fn( Term_Settings $term ) => $this->term_query_builder->build_term_query( $term ),
				$term_group->get_terms()
			);

			$relation  = $this->get_relation( $term_group->get_relation() );
			$arguments = array(
				'relation' => $relation,
			);

			return array_merge( $arguments, $term_queries );
		}

		return null;
	}
}
