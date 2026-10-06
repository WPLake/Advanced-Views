<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Meta\Field;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Meta_Field_Rule;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;

final class List_Query_Builder {
	private Meta_Field_Rule $field;
	private Field_Meta $field_meta;
	/**
	 * @var mixed
	 */
	private $meta_value;

	/**
	 * @param mixed $meta_value
	 */
	public function __construct(
		Meta_Field_Rule $field,
		Field_Meta $field_meta,
		$meta_value
	) {
		$this->field      = $field;
		$this->field_meta = $field_meta;
		$this->meta_value = $meta_value;
	}


	/**
	 * @return mixed[]
	 */
	public function build_list_query(): array {
		if ( is_array( $this->meta_value ) ) {
			if ( 0 === count( $this->meta_value ) ) {
				return array();
			}

			return $this->get_list_arguments( $this->meta_value );
		}

		return $this->get_item_arguments( $this->meta_value );
	}

	/**
	 * @param mixed[] $list_values
	 *
	 * @return array<string, mixed>
	 */
	protected function get_list_arguments( array $list_values ): array {
		$items = array_map(
			fn( $item ) => $this->get_item_arguments( $item ),
			$list_values
		);

		return array_merge(
			array(
				'relation' => 'OR',
			),
			$items
		);
	}

	/**
	 * @param mixed $item_value
	 *
	 * @return mixed[]
	 */
	protected function get_item_arguments( $item_value ): array {
		$origin_comparison = $this->field->get_comparison();
		$this->field->set_comparison( Meta_Field::resolve_list_field_comparison( $origin_comparison ) );

		$arguments = $this->get_value_arguments( $item_value );

		// restore comparison.
		$this->field->set_comparison( $origin_comparison );

		return $arguments;
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed[]
	 */
	protected function get_value_arguments( $value ): array {
		$stringified_value = Meta_Field::stringify_value( $value );
		/**
		 * Add quotes to the value. To matches exactly "123", not just 123. This prevents a match for "1234" in 'a:1:{i:0;s:4:"1234";}'.
		 *
		 * Relationship & post_object (with multiple checkbox) can be found only in this manner.
		 * https://www.advancedcustomfields.com/resources/querying-relationship-fields/.
		 */
		$value_resolver = fn() => sprintf( '"%s"', $stringified_value );

		$value_query = new Value_Query_Builder( $this->field, $this->field_meta, $value_resolver );

		return $value_query->build_value_query();
	}
}
