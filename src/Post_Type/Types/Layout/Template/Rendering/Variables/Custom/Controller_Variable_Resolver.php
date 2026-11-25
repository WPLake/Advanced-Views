<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variables\Custom;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Bridge\Controllers\Layout\Layout_Template_Controller;

use function Org\Wplake\Advanced_Views\Utils\eval_snippet;

/**
 * Gets the custom variables from the Layout's controller (the PHP code field). Pro extends it for the legacy controllers.
 */
class Controller_Variable_Resolver {
	/**
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $default_variables
	 * @param array<string,mixed> $field_values
	 * @param array<string,mixed> $custom_arguments
	 * @param \Psr\Container\ContainerInterface|null $container
	 *
	 * @return array<string,mixed>
	 */
	public function get_variables(
		string $php_code,
		string $short_unique_view_id,
		$object_id,
		array $default_variables,
		array $field_values,
		array $custom_arguments,
		$container
	): array {
		$template_controller = static::eval_template_controller( $php_code, $short_unique_view_id, $object_id, $field_values );

		return static::get_custom_controller_variables(
			$template_controller,
			$short_unique_view_id,
			$object_id,
			$default_variables,
			$field_values,
			$custom_arguments,
			$container
		);
	}

	/**
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $default_variables
	 * @param array<string,mixed> $field_values
	 * @param array<string,mixed> $custom_arguments
	 * @param \Psr\Container\ContainerInterface|null $container
	 *
	 * @return array<string,mixed>
	 */
	public function get_validation_variables(
		string $php_code,
		string $short_unique_view_id,
		$object_id,
		array $default_variables,
		array $field_values,
		array $custom_arguments,
		$container
	): array {
		$template_controller = static::eval_template_controller( $php_code, $short_unique_view_id, $object_id, $field_values );

		return static::get_custom_controller_validation_variables(
			$template_controller,
			$short_unique_view_id,
			$object_id,
			$default_variables,
			$field_values,
			$custom_arguments,
			$container
		);
	}

	/**
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $field_values
	 *
	 * @return mixed
	 */
	protected static function eval_template_controller(
		string $php_code,
		string $short_unique_view_id,
		$object_id,
		array $field_values
	) {
		return eval_snippet(
			$php_code,
			array(
				// declared variables for back compatibility.
				'_viewId'   => $short_unique_view_id,
				'_objectId' => $object_id,
				'_fields'   => $field_values,
			),
			$error
		);
	}

	/**
	 * @param mixed $template_controller
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $default_variables
	 * @param array<string,mixed> $field_values
	 * @param array<string,mixed> $custom_arguments
	 * @param \Psr\Container\ContainerInterface|null $container
	 *
	 * @return array<string, mixed>
	 */
	protected static function get_custom_controller_variables(
		$template_controller,
		string $short_unique_view_id,
		$object_id,
		array $default_variables,
		array $field_values,
		array $custom_arguments,
		$container
	): array {
		if ( $template_controller instanceof Layout_Template_Controller ) {
			static::prepare_template_controller(
				$template_controller,
				$short_unique_view_id,
				$object_id,
				$default_variables,
				$custom_arguments,
				$container
			);

			return $template_controller->get_variables();
		}

		return static::get_array_controller_variables( $template_controller );
	}

	/**
	 * @param mixed $template_controller
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $default_variables
	 * @param array<string,mixed> $field_values
	 * @param array<string,mixed> $custom_arguments
	 * @param \Psr\Container\ContainerInterface|null $container
	 *
	 * @return array<string, mixed>
	 */
	protected static function get_custom_controller_validation_variables(
		$template_controller,
		string $short_unique_view_id,
		$object_id,
		array $default_variables,
		array $field_values,
		array $custom_arguments,
		$container
	): array {
		if ( $template_controller instanceof Layout_Template_Controller ) {
			static::prepare_template_controller(
				$template_controller,
				$short_unique_view_id,
				$object_id,
				$default_variables,
				$custom_arguments,
				$container
			);

			return $template_controller->get_variables_for_validation();
		}

		return static::get_array_controller_variables( $template_controller );
	}

	/**
	 * @param int|string $object_id Can be 'options' or 'user_x'
	 * @param array<string,mixed> $default_variables
	 * @param array<string,mixed> $custom_arguments
	 * @param \Psr\Container\ContainerInterface|null $container
	 */
	protected static function prepare_template_controller(
		Layout_Template_Controller $template_controller,
		string $short_unique_view_id,
		$object_id,
		array $default_variables,
		array $custom_arguments,
		$container
	): void {
		$template_controller->set_object_id( $object_id );
		$template_controller->set_instance_id( $short_unique_view_id );
		$template_controller->set_default_variables( $default_variables );
		$template_controller->set_custom_arguments( $custom_arguments );
		$template_controller->set_container( $container );
	}

	/**
	 * Array return is allowed for back compatibility.
	 *
	 * @param mixed $template_controller
	 *
	 * @return array<string,mixed>
	 */
	protected static function get_array_controller_variables( $template_controller ): array {
		if ( is_array( $template_controller ) ) {
			/**
			 * @var array<string,mixed> $template_controller
			 */
			return $template_controller;
		}

		return array();
	}
}
