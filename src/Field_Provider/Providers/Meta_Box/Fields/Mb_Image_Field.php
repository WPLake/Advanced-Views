<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Media\Image_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class Mb_Image_Field extends Image_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$value = $field_value->get_value();

		if ( is_array( $value ) &&
			key_exists( 'ID', $value ) ) {
			$id = is_numeric( $value['ID'] ) ?
				$value['ID'] :
				0;

			$field_value->set_value( $id );
		}

		return parent::get_template_variables( $field_value );
	}
}
