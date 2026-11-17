<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Gallery_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Mb_Gallery_Field extends Gallery_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		// ids are array keys.
		$field_value->set_value(
			is_array( $field_value->get_value() ) ?
				array_keys( $field_value->get_value() ) :
				array()
		);

		return parent::get_template_variables( $field_value );
	}
}
