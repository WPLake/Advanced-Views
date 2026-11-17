<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Taxonomy_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use WP_Term;

defined( 'ABSPATH' ) || exit;

class Mb_Taxonomy_Field extends Taxonomy_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		if ( false === $field_value->get_context()->get_field_meta()->is_multiple() ) {
			$field_value->set_value(
				( $field_value->get_value() instanceof WP_Term ) ?
					$field_value->get_value()->term_id :
					0
			);
		} else {
			$field_value->set_value(
				is_array( $field_value->get_value() ) ?
					array_map(
						fn( $term ) =>  $term instanceof WP_Term ?
								$term->term_id :
								0,
						$field_value->get_value()
					) :
					array()
			);
		}

		return parent::get_template_variables( $field_value );
	}
}
