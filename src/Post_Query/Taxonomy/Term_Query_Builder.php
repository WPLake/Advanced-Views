<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Taxonomy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Tax_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Query\Core\Query_Utils;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\any;

final class Term_Query_Builder {
	const NO_VALUE_COMPARISONS = array( 'EXISTS', 'NOT EXISTS' );

	private Term_Value_Resolver $value_resolver;

	public function __construct( Term_Value_Resolver $term_value_resolver ) {
		$this->value_resolver = $term_value_resolver;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function build_term_query( Tax_Field_Settings $term ): array {
		$is_value_comparison = ! in_array( $term->comparison, self::NO_VALUE_COMPARISONS, true );
		$term_value          = $is_value_comparison ?
			$this->value_resolver->resolve_term_value( $term ) :
			null;

		$arguments = array(
			'taxonomy' => array(
				'value' => $term->taxonomy,
			),
			'operator' => array(
				'value' => $term->comparison,
			),
			'field'    => array(
				'value' => fn() => $this->get_query_field( $term_value ),
			),
			'terms'    => array(
				'condition' => $is_value_comparison,
				'value'     => $term_value,
			),
		);

		return Query_Utils::filter_arguments( $arguments );
	}

	/**
	 * @param null|mixed[] $term_value
	 */
	private function get_query_field( ?array $term_value ): string {
		$first_item = any( $term_value, 0 );

		// prefer id to slug - as WordPress has a slug-related bug.
		if ( is_null( $first_item ) ||
			is_numeric( $first_item ) ) {
			return 'term_id';
		}

		return 'slug';
	}
}
