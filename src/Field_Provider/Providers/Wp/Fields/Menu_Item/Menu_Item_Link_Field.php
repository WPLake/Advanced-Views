<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Menu_Item;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Links\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class Menu_Item_Link_Field extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$value     = $field_value->get_value();
		$menu_item = self::get_post( $value );

		$is_menu_item = is_object( $menu_item ) && 'nav_menu_item' === $menu_item->post_type;
		$field_args   = $is_menu_item ?
			self::get_menu_item_info( $menu_item ) :
			array();

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
