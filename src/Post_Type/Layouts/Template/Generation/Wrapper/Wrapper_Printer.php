<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Format_Token;

final class Wrapper_Printer {
	public static function print_row_wrapper(
		string $field_name_class,
		Layout_Settings $layout_settings,
		string $type,
		string $row_class,
		int &$tab_number,
		string $tag
	): void {
		$classes = Wrapper_Class_Resolver::resolve_row_classes( $layout_settings, $field_name_class, $type, $row_class );

		Format_Token::tabulation( $tab_number );
		self::print_opening_tag( $tag, $classes, array() );
		echo '>';
		Format_Token::next_line();

		++$tab_number;
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	public static function print_field_wrapper(
		array $field_assets,
		string $field_id,
		int &$tabs_number,
		bool $is_with_row_wrapper,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_name_class,
		string $tag
	): void {
		$attrs      = self::merge_field_wrapper_attrs( $field_assets, $field_settings, $field_id );
		$attr_class = $attrs['class'] ?? '';

		unset( $attrs['class'] );

		$classes = Wrapper_Class_Resolver::resolve_field_classes(
			$layout_settings,
			$field_settings,
			$is_with_row_wrapper,
			$field_name_class,
			$attr_class
		);

		Format_Token::tabulation( $tabs_number );
		self::print_opening_tag( $tag, $classes, $attrs );
		echo '>';

		++$tabs_number;
	}

	/**
	 * @param array<string,string> $attrs
	 */
	public static function print_attrs( array $attrs ): void {
		foreach ( $attrs as $attr => $value ) {
			printf( ' %s="%s"', esc_html( $attr ), esc_html( $value ) );
		}
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 *
	 * @return array<string,string>
	 */
	protected static function merge_field_wrapper_attrs( array $field_assets, Field_Settings $field_settings, string $field_id ): array {
		$attrs = array();

		foreach ( $field_assets as $field_asset ) {
			$asset_attrs = $field_asset->get_field_wrapper_attrs( $field_settings, $field_id );
			$attrs       = array_merge( $attrs, $asset_attrs );
		}

		return $attrs;
	}

	/**
	 * @param array<string,string> $attrs
	 */
	protected static function print_opening_tag( string $tag, string $classes, array $attrs ): void {
		printf( '<%s class="%s"', esc_html( $tag ), esc_html( $classes ) );

		self::print_attrs( $attrs );
	}

}
