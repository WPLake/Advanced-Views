<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Links\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class User_Website_Field extends Link_Field {
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
				self::URL   => $user->user_url,
				self::TITLE => $user->user_url,
			);
		}

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
