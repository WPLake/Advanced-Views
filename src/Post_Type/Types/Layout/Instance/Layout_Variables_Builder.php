<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Instance;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Source_Args_Normalizer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Instance;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use Psr\Container\ContainerInterface;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

/**
 * Builds the template variables of a Layout instance: the system ones, the fields and the custom ones.
 */
class Layout_Variables_Builder {
	private Layout_Field_Variables_Builder $field_variables_builder;
	private Layout_Controller_Variables $controller_variables;

	public function __construct(
		Layout_Field_Variables_Builder $field_variables_builder,
		Layout_Controller_Variables $controller_variables
	) {
		$this->field_variables_builder = $field_variables_builder;
		$this->controller_variables    = $controller_variables;
	}

	/**
	 * @return string[]
	 */
	public static function get_system_variable_names(): array {
		return array(
			'_view', // for back compatibility.
			Hard_Layout_Cpt::variable_name(),
		);
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

		$fields_variables  = $this->field_variables_builder->get_fields_variables( $layout, $field_values );
		$system_variables  = self::get_system_variables( $layout_settings, $classes, $object_id_text, array() );
		$default_variables = array_merge( $system_variables, $fields_variables );

		return $this->apply_custom_variables(
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
		$default_variables = self::get_system_variables( $layout_settings, $classes, '0', $items_variables );

		return $this->apply_custom_variables(
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

		$fields_variables  = $this->field_variables_builder->get_validation_fields_variables( $layout, $field_values );
		$system_variables  = self::get_system_variables( $layout_settings, $classes, '0', array() );
		$default_variables = array_merge( $system_variables, $fields_variables );

		return $this->apply_custom_validation_variables(
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
		$default_variables = self::get_system_variables( $layout_settings, $classes, '0', $items_variables );

		return $this->apply_custom_validation_variables(
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

		return $this->field_variables_builder->get_fields_variables( $item_layout, $item_field_values );
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function load_item_validation_fields( Layout_Instance $layout, int $item_id ): array {
		$item_layout       = self::with_item_source( $layout, $item_id );
		$item_field_values = array();

		return $this->field_variables_builder->get_validation_fields_variables( $item_layout, $item_field_values );
	}

	/**
	 * @param array<string,mixed> $default_variables
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $field_values
	 * @param array<string,mixed> $custom_arguments
	 *
	 * @return array<string,mixed>
	 */
	protected function apply_custom_variables(
		Layout_Settings $layout_settings,
		array $default_variables,
		$object_id,
		array $field_values,
		array $custom_arguments,
		?ContainerInterface $container
	): array {
		$short_unique_id = $layout_settings->get_unique_id( true );

		$php_variables = $this->controller_variables->get_variables(
			$layout_settings->php_variables,
			$short_unique_id,
			$object_id,
			$default_variables,
			$field_values,
			$custom_arguments,
			$container
		);

		return self::merge_custom_variables(
			$short_unique_id,
			$default_variables,
			$php_variables,
			$object_id,
			$field_values,
			false
		);
	}

	/**
	 * @param array<string,mixed> $default_variables
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $field_values
	 *
	 * @return array<string,mixed>
	 */
	protected function apply_custom_validation_variables(
		Layout_Settings $layout_settings,
		array $default_variables,
		$object_id,
		array $field_values,
		?ContainerInterface $container
	): array {
		$short_unique_id = $layout_settings->get_unique_id( true );

		$php_variables = $this->controller_variables->get_validation_variables(
			$layout_settings->php_variables,
			$short_unique_id,
			$object_id,
			$default_variables,
			$field_values,
			array(),
			$container
		);

		return self::merge_custom_variables(
			$short_unique_id,
			$default_variables,
			$php_variables,
			$object_id,
			$field_values,
			true
		);
	}

	/**
	 * @param array<string,mixed> $items_variables Empty for the Single format
	 *
	 * @return array<string,mixed>
	 */
	protected static function get_system_variables(
		Layout_Settings $layout_settings,
		string $classes,
		string $object_id,
		array $items_variables
	): array {
		$internal_variables = array_merge(
			array(
				'classes'   => $classes,
				'id'        => $layout_settings->get_markup_id(),
				// replace for others: term_6 to term-6.
				'object_id' => str_replace( '_', '-', $object_id ),
			),
			$items_variables
		);

		$system_variables = array();

		foreach ( self::get_system_variable_names() as $name ) {
			$system_variables[ $name ] = $internal_variables;
		}

		return $system_variables;
	}

	/**
	 * A copy with the item's source, so the item doesn't affect the Layout itself.
	 */
	protected static function with_item_source( Layout_Instance $layout, int $item_id ): Layout_Instance {
		$item_type = $layout->get_view_data()->item_type;
		$source    = Source::make_item( $item_type, $item_id );

		return $layout->with_source( $source );
	}

	/**
	 * @param array<string,mixed> $default_variables
	 * @param array<string,mixed> $php_variables
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $field_values
	 *
	 * @return array<string,mixed>
	 */
	protected static function merge_custom_variables(
		string $short_unique_view_id,
		array $default_variables,
		array $php_variables,
		$object_id,
		array $field_values,
		bool $is_for_validation
	): array {
		$custom_variables = self::apply_custom_variables_filter(
			$short_unique_view_id,
			$php_variables,
			$object_id,
			$field_values,
			$is_for_validation
		);

		foreach ( $custom_variables as $name => $value ) {
			$name = str_replace( '-', '_', $name );
			$name = str_replace( ' ', '_', $name );

			$default_variables[ $name ] = $value;
		}

		return $default_variables;
	}

	/**
	 * @param array<string,mixed> $php_variables
	 * @param mixed $object_id
	 * @param array<string,mixed> $field_values
	 *
	 * @return mixed[]
	 */
	protected static function apply_custom_variables_filter(
		string $short_unique_view_id,
		array $php_variables,
		$object_id,
		array $field_values,
		bool $is_for_validation
	): array {
		$custom_variables = Plugin::apply_filters(
			array(
				'advanced_views/layout/custom_variables',
				'acf_views/view/custom_variables',
			),
			$php_variables,
			$short_unique_view_id,
			$object_id,
			$field_values,
			$is_for_validation
		);
		$custom_variables = Plugin::apply_filters(
			array(
				sprintf( 'advanced_views/layout/custom_variables/layout_id=%s', $short_unique_view_id ),
				sprintf( 'acf_views/view/custom_variables/view_id=%s', $short_unique_view_id ),
			),
			$custom_variables,
			$short_unique_view_id,
			$object_id,
			$field_values,
			$is_for_validation
		);

		return arr( $custom_variables );
	}
}
