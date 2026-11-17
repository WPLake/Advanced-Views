<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\File_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Mb_File_Field extends File_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		if ( $field_value->get_context()->get_field_meta()->is_multiple() ) {
			// ids are array keys.
			$field_value->set_value(
				is_array( $field_value->get_value() ) ?
					array_keys( $field_value->get_value() ) :
					array()
			);
		} else {
			// in this case we've only the url, without any extra info
			// try to get the id from the url.
			$url = is_string( $field_value->get_value() ) ?
				$field_value->get_value() :
				'';
			$field_value->set_value(
				'' !== $url ?
					attachment_url_to_postid( $url ) :
					''
			);
		}

		return parent::get_template_variables( $field_value );
	}
}
