<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Links\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class Post_Attachment_Link extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$value = $field_value->get_value();
		$post  = self::get_post( $value );

		$is_attachment = is_object( $post ) && 'attachment' === $post->post_type;
		$field_args    = array();

		if ( $is_attachment ) {
			$title     = get_the_title( $post );
			$is_filled = strlen( $title ) > 0;

			// decode to avoid double encoding in Twig.
			$title = $is_filled ?
				html_entity_decode( $title, ENT_QUOTES ) :
				$title;

			$field_args = array(
				self::TITLE => $title,
				self::URL   => (string) wp_get_attachment_url( $post->ID ),
			);
		}

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
