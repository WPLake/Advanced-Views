<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Instance;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;

use function Org\Wplake\Advanced_Views\Utils\is_blank_value;

/**
 * Prints a Layout template with its variables through the Layout's template engine.
 */
final class Layout_Template_Printer {
	private Template_Renderer_Storage $template_renderer_storage;

	public function __construct( Template_Renderer_Storage $template_renderer_storage ) {
		$this->template_renderer_storage = $template_renderer_storage;
	}

	/**
	 * @param array<string,mixed> $variables
	 */
	public function is_empty_render( Layout_Settings $layout_settings, array $variables, int $items_count ): bool {
		return ! $layout_settings->allows_empty_rendering() &&
				self::is_empty( $layout_settings, $variables, $items_count );
	}

	/**
	 * @param array<string,mixed> $variables
	 *
	 * @return bool False if the template engine isn't available.
	 */
	public function print(
		Layout_Settings $layout_settings,
		string $template,
		array $variables,
		bool $is_for_validation
	): bool {
		$template_engine = $this->template_renderer_storage->resolve_renderer( $layout_settings->template_engine );

		if ( null === $template_engine ) {
			return false;
		}

		$template_engine->print(
			$layout_settings->get_unique_id(),
			$template,
			$variables,
			$is_for_validation
		);

		return true;
	}

	/**
	 * @param array<string,mixed> $variables
	 */
	protected static function is_empty( Layout_Settings $layout_settings, array $variables, int $items_count ): bool {
		// the Multiple format has no field variables at this level, it's empty when there are no items.
		if ( $layout_settings->is_multiple() ) {
			return 0 === $items_count;
		}

		$system_names = Layout_Variables_Builder::get_system_variable_names();

		foreach ( $variables as $variable_name => $variable_value ) {
			$is_ignored = self::is_ignored_variable( $variable_name, $variable_value, $system_names );

			if ( $is_ignored ) {
				continue;
			}

			return false;
		}

		return true;
	}

	/**
	 * @param int|string $name
	 * @param mixed $value
	 * @param string[] $system_names
	 */
	protected static function is_ignored_variable( $name, $value, array $system_names ): bool {
		$is_blank       = is_blank_value( $value );
		$is_system      = in_array( $name, $system_names, true );
		$has_value_key  = is_array( $value ) && key_exists( 'value', $value );
		$is_blank_value = $has_value_key && is_blank_value( $value['value'] );

		// ignore the system variables.
		return $is_blank || $is_system || $is_blank_value;
	}
}
