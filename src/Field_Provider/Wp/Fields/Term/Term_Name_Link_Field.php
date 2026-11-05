<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Term;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Term_Name_Link_Field extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$term = $this->get_term( $field_value->get_value() );

		if ( null === $term ) {
			$field_value->set_value( array() );

			return parent::get_template_variables( $field_value );
		}

		$term_link = get_term_link( $term );
		// can be WP_Error.
		$term_link = is_string( $term_link ) ?
			$term_link :
			'';

		$field_args = array(
			self::URL   => $term_link,
			// decode to avoid double encoding in Twig.
			self::TITLE => html_entity_decode( $term->name, ENT_QUOTES ),
		);

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
