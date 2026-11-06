<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Post_Title_Link_Field extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$post = $this->get_post( $field_value->get_value() );

		if ( null === $post ) {
			$field_value->set_value( array() );

			return parent::get_template_variables( $field_value );
		}

		$field_args = array(
			// @phpstan-ignore-next-line
			self::URL   => (string) get_the_permalink( $post ),
			// decode to avoid double encoding in Twig.
			self::TITLE => html_entity_decode( get_the_title( $post ), ENT_QUOTES ),
		);

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
