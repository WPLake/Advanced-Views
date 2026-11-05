<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;

final class Row_Class_Names {
	public static function field_name( Row_Context $context ): string {
		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$bem_name        = $layout_settings->get_bem_name();

		return sprintf(
			'%s__%s%s',
			$bem_name,
			$field_settings->id,
			$context->get_row_suffix()
		);
	}

	/**
	 * @param string[] $classes
	 */
	public static function join_with_external( array $classes, string $external_class ): string {
		$is_filled = strlen( $external_class ) > 0;

		if ( $is_filled ) {
			$classes[] = $external_class;
		}

		return join( ' ', $classes );
	}
}
