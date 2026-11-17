<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Post_Attachment_Link extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$post = $this->get_post( $field_value->get_value() );

		if ( null === $post ||
		'attachment' !== $post->post_type ) {
			$field_value->set_value( array() );
		} else {
			$title = get_the_title( $post );

			// decode to avoid double encoding in Twig.
			$title = '' !== $title ?
				html_entity_decode( $title, ENT_QUOTES ) :
				$title;

			$field_value->set_value(
				array(
					self::TITLE => $title,
					self::URL   => (string) wp_get_attachment_url( $post->ID ),
				)
			);
		}

		return parent::get_template_variables( $field_value );
	}
}
