<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\Taxonomy_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;
use WP_Term;

defined( 'ABSPATH' ) || exit;

final class Mb_Taxonomy_Field extends Taxonomy_Field {
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_meta = $field_value->get_context()->get_field_meta();
		$value      = $field_value->get_value();

		if ( $field_meta->is_multiple() ) {
			$term_ids = is_array( $value ) ?
				array_map(
					fn( $term ) => $term instanceof WP_Term ?
						$term->term_id :
						0,
					$value
				) :
				array();

			$field_value->set_value( $term_ids );
		} else {
			$term_id = $value instanceof WP_Term ?
				$value->term_id :
				0;

			$field_value->set_value( $term_id );
		}

		return parent::get_template_variables( $field_value );
	}
}
