<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Menu_Item;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Menu_Item_Link_Field extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$menu_item = $this->get_post( $field_value->get_value() );

		if ( null === $menu_item ||
			'nav_menu_item' !== $menu_item->post_type ) {
			$field_value->set_value( array() );

			return parent::get_template_variables( $field_value );
		}

		$field_args = $this->get_menu_item_info( $menu_item );

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
