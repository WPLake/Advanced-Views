<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Links;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Url_Field extends Link_Field {
	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$raw_value = $field_value->get_value();
		$value     = is_string( $raw_value ) ?
			$raw_value :
			'';
		$is_filled = strlen( $value ) > 0;

		$field_args = array();

		if ( $is_filled ) {
			$field_args = array(
				self::URL   => $value,
				self::TITLE => $field_value->get_context()->get_field_settings()->get_label_translation(),
			);
		}

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
