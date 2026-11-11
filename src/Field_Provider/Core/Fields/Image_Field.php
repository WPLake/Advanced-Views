<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Fields\Image_Field_Type;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Light_Gallery_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Lightbox\Lightbox_Library;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Image_Field extends Image_Field_Type implements Markup_Field {
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
			$attributes[ $attribute_name ] = $token_factory->variable( $field_id )
															->add_item_path( $item_key );
		}

		$inner_attributes = $this->inner_attributes( $field_id, $markup_field_data );
		$image_attrs      = array_merge( $attributes, $inner_attributes );

		return array(
			tag( 'img' )
				->attributes( $image_attrs ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

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

		$image_size = '' !== $field_context->get_field_settings()->image_size ?
			$field_context->get_field_settings()->image_size :
			'full';

		$value = is_numeric( $field_value->get_value() ) ?
			(int) $field_value->get_value() :
			0;

		if ( 0 === $value ) {
			return $args;
		}

		$image_data = wp_get_attachment_image_src( $value, $image_size );
		$image_data = false === $image_data ?
			array() :
			$image_data;

		$image_src = $image_data[0] ?? '';
		$width     = $image_data[1] ?? 0;
		$height    = $image_data[2] ?? 0;

		if ( '' === $image_src ) {
			return $args;
		}

		$alt = get_post_meta( $value, '_wp_attachment_image_alt', true );

		$args[ self::ID ]          = $value;
		$args[ self::WIDTH ]       = $width;
		$args[ self::HEIGHT ]      = $height;
		$args[ self::VALUE ]       = $image_src;
		$args[ self::ALT ]         = is_string( $alt ) || is_numeric( $alt ) ?
			$alt :
			'';
		$args[ self::FULL_SIZE ]   = (string) wp_get_attachment_image_url( $value, 'full' );
		$args[ self::CAPTION ]     = get_post_field( 'post_excerpt', $value );
		$args[ self::DESCRIPTION ] = get_post_field( 'post_content', $value );

		if ( false !== strpos( $image_src, '.svg' ) ) {
			$attached_file             = (string) get_attached_file( $value );
			$args[ self::SVG_CONTENT ] = '' !== $attached_file ?
				// @phpcs:ignore
				(string) file_get_contents( $attached_file ) :
				'';
		}

		$image_meta = wp_get_attachment_metadata( $value );

		if ( false === is_array( $image_meta ) ) {
			return $args;
		}

		$sizes_array = array( absint( $width ), absint( $height ) );
		$src_et      = (string) wp_calculate_image_srcset( $sizes_array, $image_src, $image_meta, $value );
		$sizes       = (string) wp_calculate_image_sizes( $sizes_array, $image_src, $image_meta, $value );

		if ( '' === $src_et ||
			'' === $sizes ) {
			return $args;
		}

		$args[ self::SRCSET ] = $src_et;
		$args[ self::SIZES ]  = $sizes;

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

		// repeatable fields aren't supported (they've markup like a repeater field).
		if ( null === $field_meta->get_self_repeatable_meta() ) {
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
}
