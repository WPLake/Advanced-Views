<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variables;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;

/**
 * Builds the system variables of a Layout instance (the ones available under the reserved names).
 */
final class System_Variable_Builder {
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
	 * @param array<string,mixed> $items_variables Empty for the Single format
	 *
	 * @return array<string,mixed>
	 */
	public static function get_system_variables(
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
}
