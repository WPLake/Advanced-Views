<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Light_Gallery_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Macy\Macy_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry\Masonry_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide\Splide_Library;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Gallery_Field implements Markup_Field {
	use Markup_Field_Base;

	protected Image_Field $image_field;

	public function __construct( Image_Field $image_field ) {
		$this->image_field = $image_field;
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$source_var = $token_factory->variable( $field_id )
									->add_item_path( self::VALUE );
		$item_var   = $token_factory->variable( 'image_item' );

		$item_id   = $item_var->get_name();
		$loop_body = $this->compose_item( $field_id, $item_id, $markup_field_data );

		$loop = $token_factory->loop()
						->set_source_variable( $source_var )
						->set_item_variable( $item_var )
						->set_body( $loop_body );

		return array( $loop );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_item_markup( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$markup_field_data->set_is_with_field_wrapper( true );

		return $this->image_field->compose_html( $item_id, $markup_field_data );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$args = array(
			self::VALUE => array(),
		);

		$value = is_array( $field_value->get_value() ) ?
			$field_value->get_value() :
			array();

		if ( array() === $value ) {
			return $args;
		}

		foreach ( $value as $image ) {
			$field_value->set_value( $image );

			$args[ self::VALUE ][] = $this->image_field->get_template_variables( $field_value );
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$args = array(
			self::VALUE => array(),
		);

		$value   = array();
		$value[] = $this->image_field->get_validation_template_variables( $field_value );

		return array_merge(
			$args,
			array(
				self::VALUE => $value,
			)
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return true;
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		$conditional_fields = $this->image_field->get_conditional_fields( $field_meta );

		// repeatable fields aren't supported (they've markup like a repeater field).
		if ( null === $field_meta->get_self_repeatable_meta() ) {
			$conditional_fields[] = Field_Settings::FIELD_GALLERY_TYPE;
			$conditional_fields[] = Field_Settings::FIELD_SLIDER_TYPE;
		}

		return $conditional_fields;
	}

	public function get_front_assets( Field_Settings $field_settings ): array {
		$front_assets = $this->image_field->get_front_assets( $field_settings );

		switch ( $field_settings->gallery_type ) {
			case 'masonry':
				$front_assets[] = Masonry_Library::NAME;
				break;
			case 'lightgallery_v2':
				$front_assets[] = Light_Gallery_Library::NAME;
				break;
			case 'macy_v2':
				$front_assets[] = Macy_Library::NAME;
				break;
		}

		if ( 'splide_v4' === $field_settings->slider_type ) {
			$front_assets[] = Splide_Library::NAME;
		}

		return $front_assets;
	}
}
