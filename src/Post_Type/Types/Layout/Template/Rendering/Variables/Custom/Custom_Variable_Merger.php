<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variables\Custom;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Psr\Container\ContainerInterface;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;

/**
 * Merges the custom variables (from the Layout's controller and the filters) into the default ones.
 */
class Custom_Variable_Merger {
	private Controller_Variable_Resolver $controller_variable_resolver;

	public function __construct( Controller_Variable_Resolver $controller_variable_resolver ) {
		$this->controller_variable_resolver = $controller_variable_resolver;
	}

	/**
	 * @param array<string,mixed> $default_variables
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $field_values
	 * @param array<string,mixed> $custom_arguments
	 *
	 * @return array<string,mixed>
	 */
	public function merge(
		Layout_Settings $layout_settings,
		array $default_variables,
		$object_id,
		array $field_values,
		array $custom_arguments,
		?ContainerInterface $container
	): array {
		$short_unique_id = $layout_settings->get_unique_id( true );

		$php_variables = $this->controller_variable_resolver->get_variables(
			$layout_settings->php_variables,
			$short_unique_id,
			$object_id,
			$default_variables,
			$field_values,
			$custom_arguments,
			$container
		);

		return self::merge_variables(
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
	public function merge_for_validation(
		Layout_Settings $layout_settings,
		array $default_variables,
		$object_id,
		array $field_values,
		?ContainerInterface $container
	): array {
		$short_unique_id = $layout_settings->get_unique_id( true );

		$php_variables = $this->controller_variable_resolver->get_validation_variables(
			$layout_settings->php_variables,
			$short_unique_id,
			$object_id,
			$default_variables,
			$field_values,
			array(),
			$container
		);

		return self::merge_variables(
			$short_unique_id,
			$default_variables,
			$php_variables,
			$object_id,
			$field_values,
			true
		);
	}

	/**
	 * @param array<string,mixed> $default_variables
	 * @param array<string,mixed> $php_variables
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $field_values
	 *
	 * @return array<string,mixed>
	 */
	protected static function merge_variables(
		string $short_unique_view_id,
		array $default_variables,
		array $php_variables,
		$object_id,
		array $field_values,
		bool $is_for_validation
	): array {
		$custom_variables = self::apply_variables_filter(
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
	protected static function apply_variables_filter(
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
