<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;

/**
 * @phpstan-import-type Tag_Stack from Html
 * @phpstan-import-type Tag from Html
 */
final class Outer_Printer {
	/**
	 * @param Template_Pattern[] $field_assets
	 * @param callable(bool $is_with_outers): void $print_content
	 *
	 * @return Tag_Stack
	 */
	public static function wrap_with_outers(
		array $field_assets,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $row_type,
		int &$tabs_number,
		Token_Factory $token_factory,
		callable $print_content
	): array {
		$field_outers   = self::get_field_outers( $field_assets, $layout_settings, $field_settings, $field_id, $row_type );
		$is_with_outers = array() !== $field_outers;
		$content        = fn() => $print_content( $is_with_outers );

		// the innermost outer goes first.
		foreach ( array_reverse( $field_outers ) as $outer ) {
			$content = Wrapper_Printer::wrap(
				self::outer_tag( $outer, $token_factory ),
				$tabs_number,
				true,
				true,
				$content
			);
		}

		return is_array( $content ) ?
			$content :
			Html::stack( $content );
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 *
	 * @return Html_Wrapper[]
	 */
	protected static function get_field_outers(
		array $field_assets,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $row_type
	): array {
		$assets_outers = array();

		foreach ( $field_assets as $field_asset ) {
			$assets_outers[] = $field_asset->get_field_outers( $layout_settings, $field_settings, $field_id, $row_type );
		}

		return Template_Field_Data::merge_outers( $assets_outers );
	}

	/**
	 * @return Tag
	 */
	protected static function outer_tag( Html_Wrapper $outer, Token_Factory $token_factory ): array {
		$attrs = $outer->attrs;

		foreach ( $outer->variable_attrs as $attr => $variable_info ) {
			$attrs[ $attr ] = $token_factory->variable( $variable_info['field_id'] )
											->add_item_path( $variable_info['item_key'] );
		}

		return Html::tag( $outer->tag, $attrs );
	}
}
