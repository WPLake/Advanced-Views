<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Wrapper_Printer {
	public static function row_tag(
		string $field_name_class,
		Layout_Settings $layout_settings,
		string $type,
		string $row_class,
		string $tag
	): Html_Tag {
		$classes = Wrapper_Class_Resolver::resolve_row_classes( $layout_settings, $field_name_class, $type, $row_class );

		return tag( $tag )
			->attributes( array( 'class' => $classes ) );
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	public static function field_tag(
		array $field_assets,
		string $field_id,
		bool $is_with_row_wrapper,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_name_class,
		string $tag
	): Html_Tag {
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

		$class_attrs = array( 'class' => $classes );
		$field_attrs = array_merge( $class_attrs, $attrs );

		return tag( $tag )
			->attributes( $field_attrs );
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
