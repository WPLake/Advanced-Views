<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Format_Token;
use function Org\Wplake\Advanced_Views\Template\Html\stack;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Template\Html\wrap;

/**
 * @phpstan-import-type Tag from Html
 * @phpstan-import-type Html_Node from Html
 * @phpstan-import-type Tag_Stack from Html
 */
final class Wrapper_Printer {
	/**
	 * @return Tag
	 */
	public static function row_tag(
		string $field_name_class,
		Layout_Settings $layout_settings,
		string $type,
		string $row_class,
		string $tag
	): array {
		$classes = Wrapper_Class_Resolver::resolve_row_classes( $layout_settings, $field_name_class, $type, $row_class );

		return tag( $tag, array( 'class' => $classes ) );
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 *
	 * @return Tag
	 */
	public static function field_tag(
		array $field_assets,
		string $field_id,
		bool $is_with_row_wrapper,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_name_class,
		string $tag
	): array {
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

		return tag( $tag, array_merge( array( 'class' => $classes ), $attrs ) );
	}

	/**
	 * Returns the group of the tag with the inner content inside it, and the formatting (tabs, new lines) around it,
	 * which is kept in callbacks.
	 *
	 * @param Tag $tag
	 * @param Html_Node $inner
	 *
	 * @return Tag_Stack
	 */
	public static function wrap(
		array $tag,
		int &$tabs_number,
		bool $is_new_line_before,
		bool $is_new_line_after_open,
		$inner
	): array {
		$print_opening_tabs = static function () use ( &$tabs_number, $is_new_line_before ) {
			if ( $is_new_line_before ) {
				Format_Token::next_line();
			}

			Format_Token::tabulation( $tabs_number );
		};

		$print_inner_start = static function () use ( &$tabs_number, $is_new_line_after_open ) {
			if ( $is_new_line_after_open ) {
				Format_Token::next_line();
			}

			++$tabs_number;
		};

		$print_closing_tabs = static function () use ( &$tabs_number ) {
			Format_Token::tabulation( --$tabs_number );
		};

		return stack(
			$print_opening_tabs,
			wrap(
				$tag,
				stack(
					$print_inner_start,
					$inner,
					$print_closing_tabs
				)
			),
			static fn() => Format_Token::next_line()
		);
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
}
