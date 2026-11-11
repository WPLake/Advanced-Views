<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Library_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

defined( 'ABSPATH' ) || exit;

interface Layout_Pattern extends Library_Pattern {
	public function maybe_activate_layout( Layout_Settings $layout_settings ): void;

	/**
	 * @return array<string,string>
	 */
	public function generate_layout_css( Layout_Settings $layout_settings ): array;

	/**
	 * @return array<string,string>
	 */
	public function generate_layout_js( Layout_Settings $layout_settings ): array;

	public function get_row_wrapper_class( string $row_type ): string;

	public function get_row_wrapper_tag( Field_Settings $field_settings, string $row_type ): string;

	public function get_field_wrapper_tag( Field_Settings $field_settings, string $row_type ): string;

	/**
	 * @return array<string,string>
	 */
	public function get_field_wrapper_attrs( Field_Settings $field_settings, string $field_id ): array;

	/**
	 * @return Html_Tag[]
	 */
	public function get_field_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $row_type,
		Token_Factory $token_factory
	): array;

	/**
	 * @return Html_Tag[]
	 */
	public function get_item_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $item_id,
		Token_Factory $token_factory
	): array;

	/**
	 * @return array<string,array{field_id:string,item_key:string,}>
	 */
	public function get_inner_variable_attributes( Field_Settings $field_settings, string $field_id ): array;

	public function is_label_out_of_row(): bool;
}
