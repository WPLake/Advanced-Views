<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Links\Link_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

final class Post_Title_Link_Field extends Link_Field {
	use Custom_Field;

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$value = $field_value->get_value();
		$post  = self::get_post( $value );

		$field_args = array();

		if ( is_object( $post ) ) {
			// @phpstan-ignore-next-line
			$url   = (string) get_the_permalink( $post );
			$title = get_the_title( $post );

			$field_args = array(
				self::URL   => $url,
				// decode to avoid double encoding in Twig.
				self::TITLE => html_entity_decode( $title, ENT_QUOTES ),
			);
		}

		$field_value->set_value( $field_args );

		return parent::get_template_variables( $field_value );
	}
}
