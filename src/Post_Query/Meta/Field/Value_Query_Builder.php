<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Meta\Field;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Meta_Field_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Query\Core\Query_Utils;

final class Value_Query_Builder {
	private Meta_Field_Settings $field;
	private Field_Meta $field_meta;
	/**
	 * @var callable(string $value):mixed
	 */
	private $resolve_value;

	/**
	 * @param callable(string $value):mixed $resolve_value
	 */
	public function __construct(
		Meta_Field_Settings $field,
		Field_Meta $field_meta,
		callable $resolve_value
	) {
		$this->field         = $field;
		$this->field_meta    = $field_meta;
		$this->resolve_value = $resolve_value;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function build_value_query(): array {
		$is_value_comparison = Meta_Field::is_value_comparison( $this->field->comparison );
		$is_numeric_type     = Meta_Field::is_numeric_field( $this->field_meta->get_type() );
		$value               = $is_value_comparison ?
			$this->get_value() :
			null;

		$arguments = array(
			'key'     => array(
				'value' => $this->field_meta->get_name(),
			),
			'compare' => array(
				'value' => fn() => $this->get_comparison( $value ),
			),
			'value'   => array(
				'condition' => $is_value_comparison,
				'value'     => $value,
			),
			'type'    => array(
				'condition' => $is_value_comparison && $is_numeric_type,
				'value'     => 'NUMERIC',
			),
		);

		return Query_Utils::filter_arguments( $arguments );
	}

	/**
	 * @return mixed
	 */
	protected function get_value() {
		$is_object_field = Meta_Field::is_object_field( $this->field_meta->get_type() );
		$value           = ( $this->resolve_value )( $this->field->resolve_value() );

		if ( $is_object_field ) {
			return Meta_Field::stringify_value( $value );
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 */
	protected function get_comparison( $value ): string {
		$origin_comparison = $this->field->comparison;

		if ( is_array( $value ) ) {
			return Meta_Field::resolve_list_value_comparison( $origin_comparison );
		}

		return $origin_comparison;
	}
}
