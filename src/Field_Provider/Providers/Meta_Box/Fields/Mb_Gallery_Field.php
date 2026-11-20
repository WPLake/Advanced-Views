<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Media\Gallery_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class Mb_Gallery_Field extends Gallery_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$value = $field_value->get_value();

		// ids are array keys.
		$ids = is_array( $value ) ?
			array_keys( $value ) :
			array();

		$field_value->set_value( $ids );

		return parent::get_template_variables( $field_value );
	}
}
