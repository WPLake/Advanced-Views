<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\File_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Media\Gallery_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Media\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

defined( 'ABSPATH' ) || exit;

final class Pods_Upload_Field implements Markup_Field {
	use Markup_Field_Base;

	private Image_Field $image_field;
	private File_Field $file_field;
	private Gallery_Field $gallery_field;

	public function __construct( Image_Field $image_field, File_Field $file_field, Gallery_Field $gallery_field ) {
		$this->image_field   = $image_field;
		$this->file_field    = $file_field;
		$this->gallery_field = $gallery_field;
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$field_context  = $markup_field_data->get_context();
		$field_meta     = $field_context->get_field_meta();
		$field_instance = $this->get_field_instance( $field_meta );

		return $field_instance->compose_html( $field_id, $markup_field_data );
	}

	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();
		$field_meta    = $field_context->get_field_meta();
		$is_multiple   = $field_meta->is_multiple();
		$origin_value  = $field_value->get_value();

		$new_value = self::get_normalized_value( $origin_value, $is_multiple );
		$field_value->set_value( $new_value );

		$field_instance = $this->get_field_instance( $field_meta );

		return $field_instance->get_template_variables( $field_value );
	}

	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$field_context  = $field_value->get_context();
		$field_meta     = $field_context->get_field_meta();
		$field_instance = $this->get_field_instance( $field_meta );

		return $field_instance->get_validation_template_variables( $field_value );
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		$field_instance = $this->get_field_instance( $field_meta );

		return $field_instance->is_with_field_wrapper(
			$layout_settings,
			$field_settings,
			$field_meta
		);
	}

	public function get_conditional_fields( Field_Meta $field_meta ): array {
		$field_instance = $this->get_field_instance( $field_meta );

		return $field_instance->get_conditional_fields( $field_meta );
	}

	public function get_front_assets( Field_Settings $field_settings ): array {
		$field_meta     = $field_settings->get_field_meta();
		$field_instance = $this->get_field_instance( $field_meta );

		return $field_instance->get_front_assets( $field_settings );
	}

	protected function get_field_instance( Field_Meta $field_meta ): Markup_Field {
		$return_format = $field_meta->get_return_format();
		$is_images     = in_array(
			$return_format,
			array(
				'images',
				'images-any',
			),
			true
		);

		if ( $is_images ) {
			return $field_meta->is_multiple() ?
				$this->gallery_field :
				$this->image_field;
		}

		return $this->file_field;
	}

	/**
	 * @param mixed $value
	 *
	 * @return int|int[]
	 */
	protected static function get_normalized_value( $value, bool $is_multiple ) {
		if ( $is_multiple ) {
			return self::get_attachment_ids( $value );
		}

		return self::get_attachment_id( $value );
	}

	/**
	 * @param mixed $value
	 *
	 * @return int[]
	 */
	protected static function get_attachment_ids( $value ): array {
		if ( is_array( $value ) ) {
			return array_map(
				fn( $item ) => self::get_attachment_id( $item ),
				$value
			);
		}

		return array();
	}

	/**
	 * @param mixed $value
	 */
	protected static function get_attachment_id( $value ): int {
		if ( is_array( $value ) ) {
			$id = int( $value, 'ID' );

			// in Pod Blocks 'id' is in lower case.
			return 0 !== $id ?
				$id :
				int( $value, 'id' );
		}

		return 0;
	}
}
