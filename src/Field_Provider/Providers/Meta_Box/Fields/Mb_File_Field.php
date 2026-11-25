<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\File_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class Mb_File_Field extends File_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_meta = $field_value->get_context()->get_field_meta();
		$value      = $field_value->get_value();

		if ( $field_meta->is_multiple() ) {
			// ids are array keys.
			$ids = is_array( $value ) ?
				array_keys( $value ) :
				array();

			$field_value->set_value( $ids );
		} else {
			// in this case we've only the url, without any extra info
			// try to get the id from the url.
			$url        = is_string( $value ) ?
				$value :
				'';
			$is_filled  = strlen( $url ) > 0;
			$attachment = $is_filled ?
				attachment_url_to_postid( $url ) :
				'';

			$field_value->set_value( $attachment );
		}

		return parent::get_template_variables( $field_value );
	}
}
