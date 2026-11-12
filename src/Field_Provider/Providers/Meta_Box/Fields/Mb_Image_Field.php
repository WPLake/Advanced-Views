<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Mb_Image_Field extends Image_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		if ( is_array( $field_value->get_value() ) &&
			key_exists( 'ID', $field_value->get_value() ) ) {
			$id = is_numeric( $field_value->get_value()['ID'] ) ?
				$field_value->get_value()['ID'] :
				0;

			$field_value->set_value( $id );
		}

		return parent::get_template_variables( $field_value );
	}
}
