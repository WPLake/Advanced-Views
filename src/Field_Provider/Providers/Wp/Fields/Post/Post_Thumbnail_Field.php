<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Media\Image_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Light_Gallery_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Lightbox_Library;

final class Post_Thumbnail_Field extends Image_Field {
	use Custom_Field;

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$value = $field_value->get_value();
		$post  = self::get_post( $value );

		$image_id = 0;

		if ( is_object( $post ) ) {
			$image_id = 'attachment' === $post->post_type ?
				$post->ID :
				(int) get_post_thumbnail_id( $post );
		}

		$field_value->set_value( $image_id );

		return parent::get_template_variables( $field_value );
	}

	public function get_front_assets( Field_Settings $field_settings ): array {
		$front_assets = array();

		switch ( $field_settings->lightbox_type ) {
			case 'simple':
				$front_assets[] = Lightbox_Library::NAME;
				break;
			case 'lightgallery_v2':
				$front_assets[] = Light_Gallery_Library::NAME;
				break;
		}

		return array_merge( parent::get_front_assets( $field_settings ), $front_assets );
	}
}
