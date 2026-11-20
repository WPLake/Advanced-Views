<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Links\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class User_Author_Link_Field extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$value = $field_value->get_value();
		$user  = self::get_user( $value );

		$field_args = array();

		if ( is_object( $user ) ) {
			$field_args = array(
				self::URL   => get_author_posts_url( $user->ID ),
				// decode to avoid double encoding in Twig.
				self::TITLE => html_entity_decode( $user->display_name, ENT_QUOTES ),
			);
		}

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
