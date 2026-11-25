<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Attributes\Source_Args_Normalizer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Instance;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variables\Custom\Custom_Variable_Merger;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variables\Field_Variable_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variables\System_Variable_Builder;
use Psr\Container\ContainerInterface;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

/**
 * Builds the template variables of a Layout instance: the system ones, the fields and the custom ones (each built by its own unit).
 */
class Variable_Builder {
	private Field_Variable_Builder $field_variable_builder;
	private Custom_Variable_Merger $custom_variable_merger;

	public function __construct(
		Field_Variable_Builder $field_variable_builder,
		Custom_Variable_Merger $custom_variable_merger
	) {
		$this->field_variable_builder = $field_variable_builder;
		$this->custom_variable_merger = $custom_variable_merger;
	}

	/**
	 * @param array<string,mixed> $custom_arguments
	 *
	 * @return array<string,mixed>
	 */
	public function build(
		Layout_Instance $layout,
		string $classes,
		array $custom_arguments,
		?ContainerInterface $container
	): array {
		$is_multiple = $layout->get_view_data()->is_multiple();

		return $is_multiple ?
			$this->build_multiple( $layout, $classes, $custom_arguments, $container ) :
			$this->build_single( $layout, $classes, $custom_arguments, $container );
	}

	/**
	 * Same as build(), but with fake data: no source, no loaded values, the only item is 0, and no custom arguments.
	 *
	 * @return array<string,mixed>
	 */
	public function build_for_validation(
		Layout_Instance $layout,
		string $classes,
		?ContainerInterface $container
	): array {
		$is_multiple = $layout->get_view_data()->is_multiple();

		return $is_multiple ?
			$this->build_multiple_validation( $layout, $classes, $container ) :
			$this->build_single_validation( $layout, $classes, $container );
	}

	/**
	 * @param array<string,mixed> $custom_arguments
	 *
	 * @return array<string,mixed>
	 */
	protected function build_single(
		Layout_Instance $layout,
		string $classes,
		array $custom_arguments,
		?ContainerInterface $container
	): array {
		$layout_settings = $layout->get_view_data();
		$object_id       = $layout->get_source()->get_id();
		$object_id_text  = strval( $object_id );
		$field_values    = array();

		$fields_variables  = $this->field_variable_builder->get_fields_variables( $layout, $field_values );
		$system_variables  = System_Variable_Builder::get_system_variables( $layout_settings, $classes, $object_id_text, array() );
		$default_variables = array_merge( $system_variables, $fields_variables );

		return $this->custom_variable_merger->merge(
			$layout_settings,
			$default_variables,
			$object_id,
			$field_values,
			$custom_arguments,
			$container
		);
	}

	/**
	 * @param array<string,mixed> $custom_arguments
	 *
	 * @return array<string,mixed>
	 */
	protected function build_multiple(
		Layout_Instance $layout,
		string $classes,
		array $custom_arguments,
		?ContainerInterface $container
	): array {
		$layout_settings = $layout->get_view_data();
		$item_ids        = $layout->get_item_ids();
		$load_fields     = fn( int $item_id ): array => $this->load_item_fields( $layout, $item_id );

		$items_variables   = $this->get_items_variables( $layout, $item_ids, $load_fields );
		$default_variables = System_Variable_Builder::get_system_variables( $layout_settings, $classes, '0', $items_variables );

		return $this->custom_variable_merger->merge(
			$layout_settings,
			$default_variables,
			'0',
			array(),
			$custom_arguments,
			$container
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function build_single_validation(
		Layout_Instance $layout,
		string $classes,
		?ContainerInterface $container
	): array {
		$layout_settings = $layout->get_view_data();
		$field_values    = array();

		$fields_variables  = $this->field_variable_builder->get_validation_fields_variables( $layout, $field_values );
		$system_variables  = System_Variable_Builder::get_system_variables( $layout_settings, $classes, '0', array() );
		$default_variables = array_merge( $system_variables, $fields_variables );

		return $this->custom_variable_merger->merge_for_validation(
			$layout_settings,
			$default_variables,
			'0',
			$field_values,
			$container
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function build_multiple_validation(
		Layout_Instance $layout,
		string $classes,
		?ContainerInterface $container
	): array {
		$layout_settings = $layout->get_view_data();
		$load_fields     = fn( int $item_id ): array => $this->load_item_validation_fields( $layout, $item_id );

		$items_variables   = $this->get_items_variables( $layout, array( 0 ), $load_fields );
		$default_variables = System_Variable_Builder::get_system_variables( $layout_settings, $classes, '0', $items_variables );

		return $this->custom_variable_merger->merge_for_validation(
			$layout_settings,
			$default_variables,
			'0',
			array(),
			$container
		);
	}

	/**
	 * In the Multiple format the fields are loaded per item, via the 'load_fields' closure.
	 *
	 * @param int[] $item_ids
	 * @param callable(int):array<string,mixed> $load_fields
	 *
	 * @return array<string,mixed>
	 */
	protected function get_items_variables( Layout_Instance $layout, array $item_ids, callable $load_fields ): array {
		$item_type = $layout->get_view_data()->item_type;
		$items_key = Source_Args_Normalizer::ITEM_ARGS[ $item_type ] ?? '';

		return array(
			$items_key    => array(
				'ids' => $item_ids,
			),
			'load_fields' => fn( $item_id ): array => $load_fields( int( $item_id ) ),
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function load_item_fields( Layout_Instance $layout, int $item_id ): array {
		$item_layout       = self::with_item_source( $layout, $item_id );
		$item_field_values = array();

		return $this->field_variable_builder->get_fields_variables( $item_layout, $item_field_values );
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function load_item_validation_fields( Layout_Instance $layout, int $item_id ): array {
		$item_layout       = self::with_item_source( $layout, $item_id );
		$item_field_values = array();

		return $this->field_variable_builder->get_validation_fields_variables( $item_layout, $item_field_values );
	}

	/**
	 * A copy with the item's source, so the item doesn't affect the Layout itself.
	 */
	protected static function with_item_source( Layout_Instance $layout, int $item_id ): Layout_Instance {
		$item_type = $layout->get_view_data()->item_type;
		$source    = Source::make_item( $item_type, $item_id );

		return $layout->with_source( $source );
	}
}
