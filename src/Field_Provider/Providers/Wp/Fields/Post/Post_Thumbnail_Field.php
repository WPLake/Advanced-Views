<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Light_Gallery_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Lightbox\Lightbox_Library;

class Post_Thumbnail_Field extends Image_Field {
	use Custom_Field;

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$post = $this->get_post( $field_value->get_value() );

		if ( null === $post ) {
			$field_value->set_value( 0 );

			return parent::get_template_variables( $field_value );
		}

		$image_id = 'attachment' !== $post->post_type ?
			(int) get_post_thumbnail_id( $post ) :
			$post->ID;

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
