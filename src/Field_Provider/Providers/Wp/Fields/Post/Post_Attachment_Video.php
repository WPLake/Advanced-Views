<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

class Post_Attachment_Video implements Markup_Field {
	use Markup_Field_Base;

	const MIME_TYPE = 'mime_type';

	use Custom_Field;

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$src_var  = $token_factory->variable( $field_id )
									->add_item_path( self::VALUE );
		$type_var = $token_factory->variable( $field_id )
									->add_item_path( self::MIME_TYPE );

		$video_class = $this->get_field_class( 'element', $markup_field_data );


		return array(
			tag( 'video' )
				->attributes( array(
					'class'    => $video_class,
					'controls' => true,
				) )
				->content(
					tag( 'source' )
						->attributes( array(
							'src'  => $src_var,
							'type' => $type_var,
						) )
				),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$post = $this->get_post( $field_value->get_value() );

		if ( null === $post ||
			'attachment' !== $post->post_type ||
			0 !== strpos( $post->post_mime_type, 'video/' ) ) {
			return array(
				self::VALUE     => '',
				self::MIME_TYPE => '',
			);
		}

		$attachment_url = (string) wp_get_attachment_url( $post->ID );

		return array(
			self::VALUE     => $attachment_url,
			self::MIME_TYPE => $post->post_mime_type,
		);
	}

	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		return array(
			self::VALUE     => 'https://site.com/video.mp4',
			self::MIME_TYPE => 'video/mp4',
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return false;
	}
}
