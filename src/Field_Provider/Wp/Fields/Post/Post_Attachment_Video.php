<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Post;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use Org\Wplake\Advanced_Views\Template\Html\Html;

class Post_Attachment_Video extends Markup_Field_Base {
	const MIME_TYPE = 'mime_type';

	use Custom_Field;

	public function compose_html( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$src_var  = $token_factory->variable( $field_id )
									->add_item_path( self::VALUE );
		$type_var = $token_factory->variable( $field_id )
									->add_item_path( self::MIME_TYPE );

		return Html::wrap(
			Html::tag(
				'video',
				array(
					'class'    => $this->get_field_class( 'element', $markup_field_data ),
					'controls' => '',
				)
			),
			Html::stack(
				fn() => $markup_field_data->print_new_line_and_increment_tabs(),
				Html::tag(
					'source',
					array(
						'src'  => $src_var,
						'type' => $type_var,
					)
				),
				fn() => $markup_field_data->print_new_line_and_decrement_tabs()
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$post = $this->get_post( $variable_field_data->get_value() );

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

	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
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
