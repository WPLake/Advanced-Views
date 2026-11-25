<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Media;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Field_Types\Image_Field_Type;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Light_Gallery_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Lightbox_Library;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Image_Field implements Image_Field_Type, Markup_Field {
	use Markup_Field_Base;

	const ALT         = 'alt';
	const DESCRIPTION = 'description';
	const SRCSET      = 'srcset';
	const SIZES       = 'sizes';
	const DECODING    = 'decoding';
	const LOADING     = 'loading';
	const SVG_CONTENT = 'svg_content';
	const ID          = 'id';

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory  = $markup_field_data->get_token_factory();
		$attributes_map = array(
			'src'      => self::VALUE,
			'width'    => self::WIDTH,
			'height'   => self::HEIGHT,
			'alt'      => self::ALT,
			'decoding' => self::DECODING,
			'loading'  => self::LOADING,
			'srcset'   => self::SRCSET,
			'sizes'    => self::SIZES,
		);

		$attributes = array(
			'class' => $this->get_field_class( 'image', $markup_field_data ),
		);

		foreach ( $attributes_map as $attribute_name => $item_key ) {
			$attributes[ $attribute_name ] = $token_factory
				->variable( $field_id )
				->add_item_path( $item_key );
		}

		$inner_attributes = $this->inner_attributes( $field_id, $markup_field_data );
		$image_attrs      = array_merge( $attributes, $inner_attributes );

		$image_tag = tag( 'img' )->attributes( $image_attrs );

		return array( $image_tag );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_settings = $field_value->get_context()->get_field_settings();

		$args = array(
			self::WIDTH       => 0,
			self::HEIGHT      => 0,
			self::VALUE       => '',
			self::ALT         => '',
			self::CAPTION     => '',
			self::DESCRIPTION => '',
			self::SRCSET      => '',
			self::SIZES       => '',
			self::DECODING    => 'async',
			self::LOADING     => 'lazy',
			self::FULL_SIZE   => '',
			self::SVG_CONTENT => '',
			// for cases when image is used inside a gallery,
			// it allows to get image meta fields (using another View shortcode).
			self::ID          => 0,
		);

		$image_size = '' !== $field_settings->image_size ?
			$field_settings->image_size :
			'full';

		$raw_value     = $field_value->get_value();
		$attachment_id = is_numeric( $raw_value ) ?
			(int) $raw_value :
			0;

		$image_data = $this->get_image_data( $attachment_id, $image_size );
		$image_src  = $image_data[0] ?? '';
		$is_found   = strlen( $image_src ) > 0;

		if ( $is_found ) {
			$image_args = $this->compose_image_variables( $attachment_id, $image_data );

			return array_merge( $args, $image_args );
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$args = array(
			self::WIDTH       => 0,
			self::HEIGHT      => 0,
			self::VALUE       => '',
			self::ALT         => '',
			self::CAPTION     => '',
			self::DESCRIPTION => '',
			self::SRCSET      => '',
			self::SIZES       => '',
			self::DECODING    => 'async',
			self::LOADING     => 'lazy',
			self::FULL_SIZE   => '',
			self::ID          => 0,
			self::SVG_CONTENT => '',
		);

		return array_merge(
			$args,
			array(
				self::WIDTH       => 1,
				self::HEIGHT      => 1,
				self::VALUE       => 'https://wordpress.org/',
				self::ALT         => 'wordpress.org',
				self::CAPTION     => 'wp.org screenshot',
				self::DESCRIPTION => 'wp.org screenshot',
				self::SRCSET      => 'https://wordpress.org/ 1w',
				self::SIZES       => '(max-width: 1px) 1vw',
				self::FULL_SIZE   => 'https://wordpress.org/',
				self::SVG_CONTENT => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"></svg>',
			)
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return $layout_settings->is_with_unnecessary_wrappers;
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		$conditional_fields = array( Field_Settings::FIELD_IMAGE_SIZE );

		$self_repeatable_meta = $field_meta->get_self_repeatable_meta();

		// repeatable fields aren't supported (they've markup like a repeater field).
		if ( null === $self_repeatable_meta ) {
			$conditional_fields = array_merge(
				$conditional_fields,
				array(
					Field_Settings::FIELD_LIGHTBOX_TYPE,
					Field_Settings::FIELD_GALLERY_WITH_LIGHT_BOX,
				)
			);
		}

		return $conditional_fields;
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

		return $front_assets;
	}

	/**
	 * @return array<string,string|Variable_Token>
	 */
	protected function inner_attributes( string $field_id, Template_Field_Meta $markup_field_data ): array {
		return $markup_field_data->get_item_attributes( $field_id );
	}

	/**
	 * @return array{0?: string, 1?: int, 2?: int, 3?: bool}
	 */
	protected function get_image_data( int $attachment_id, string $image_size ): array {
		$image_data = 0 !== $attachment_id ?
			wp_get_attachment_image_src( $attachment_id, $image_size ) :
			false;

		return is_array( $image_data ) ?
			$image_data :
			array();
	}

	/**
	 * @param array{0?: string, 1?: int, 2?: int, 3?: bool} $image_data
	 *
	 * @return array<string, mixed>
	 */
	protected function compose_image_variables( int $attachment_id, array $image_data ): array {
		$image_src = $image_data[0] ?? '';
		$width     = $image_data[1] ?? 0;
		$height    = $image_data[2] ?? 0;

		$alt         = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		$is_alt_text = is_string( $alt ) || is_numeric( $alt );
		$full_size   = (string) wp_get_attachment_image_url( $attachment_id, 'full' );
		$caption     = get_post_field( 'post_excerpt', $attachment_id );
		$description = get_post_field( 'post_content', $attachment_id );
		$svg_content = $this->get_svg_content( $attachment_id, $image_src );
		$srcset_args = $this->get_srcset_variables( $attachment_id, $image_src, $width, $height );

		$image_args = array(
			self::ID          => $attachment_id,
			self::WIDTH       => $width,
			self::HEIGHT      => $height,
			self::VALUE       => $image_src,
			self::ALT         => $is_alt_text ?
				$alt :
				'',
			self::FULL_SIZE   => $full_size,
			self::CAPTION     => $caption,
			self::DESCRIPTION => $description,
			self::SVG_CONTENT => $svg_content,
		);

		return array_merge( $image_args, $srcset_args );
	}

	protected function get_svg_content( int $attachment_id, string $image_src ): string {
		$is_svg = is_int( strpos( $image_src, '.svg' ) );

		if ( $is_svg ) {
			$attached_file = (string) get_attached_file( $attachment_id );
			$is_filled     = strlen( $attached_file ) > 0;

			return $is_filled ?
				// @phpcs:ignore
				(string) file_get_contents( $attached_file ) :
				'';
		}

		return '';
	}

	/**
	 * @return array<string, string>
	 */
	protected function get_srcset_variables( int $attachment_id, string $image_src, int $width, int $height ): array {
		$image_meta = wp_get_attachment_metadata( $attachment_id );

		if ( is_array( $image_meta ) ) {
			$sizes_array = array( absint( $width ), absint( $height ) );
			$srcset      = (string) wp_calculate_image_srcset(
				$sizes_array,
				$image_src,
				$image_meta,
				$attachment_id
			);
			$sizes       = (string) wp_calculate_image_sizes(
				$sizes_array,
				$image_src,
				$image_meta,
				$attachment_id
			);

			if ( strlen( $srcset ) > 0 && strlen( $sizes ) > 0 ) {
				return array(
					self::SRCSET => $srcset,
					self::SIZES  => $sizes,
				);
			}
		}

		return array();
	}
}
