<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Gallery_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Woo_Gallery_Field extends Gallery_Field {
	use Custom_Field;

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$args = array(
			self::VALUE => array(),
		);

		$product = $this->get_product( $field_value->get_value() );

		if ( null === $product ) {
			return $args;
		}

		$image_ids = $product->get_gallery_image_ids();

		foreach ( $image_ids as $image_id ) {
			$field_value->set_value( $image_id );

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
}
