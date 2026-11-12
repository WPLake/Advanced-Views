<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\File_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Gallery_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

defined( 'ABSPATH' ) || exit;

class Pods_Upload_Field implements Markup_Field {
	use Markup_Field_Base;

	private Image_Field $image_field;
	private File_Field $file_field;
	private Gallery_Field $gallery_field;

	public function __construct( Image_Field $image_field, File_Field $file_field, Gallery_Field $gallery_field ) {
		$this->image_field   = $image_field;
		$this->file_field    = $file_field;
		$this->gallery_field = $gallery_field;
	}

	protected function get_field_instance( Field_Meta $field_meta ): Markup_Field {
		if ( ! in_array( $field_meta->get_return_format(), array( 'images', 'images-any' ), true ) ) {
			return $this->file_field;
		}

		return $field_meta->is_multiple() ?
			$this->gallery_field :
			$this->image_field;
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		return $this->get_field_instance( $markup_field_data->get_context()->get_field_meta() )
			->compose_html( $field_id, $markup_field_data );
	}

	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

		if ( $field_context->get_field_meta()->is_multiple() ) {
			if ( is_array( $field_value->get_value() ) ) {
				$field_value->set_value(
					array_map(
						function ( $value ) {
							if ( false === is_array( $value ) ) {
								return 0;
							}

							$id = int( $value, 'ID' );

							// in Pod Blocks 'id' is in lower case.
							return 0 !== $id ?
								$id :
								int( $value, 'id' );
						},
						$field_value->get_value()
					)
				);
			} else {
				$field_value->set_value( array() );
			}
		} elseif ( is_array( $field_value->get_value() ) ) {
			$id = int( $field_value->get_value(), 'ID' );
			// in Pod Blocks 'id' is in lower case.
			$field_value->set_value(
				0 !== $id ?
					$id :
					int( $field_value->get_value(), 'id' )
			);
		} else {
			$field_value->set_value( 0 );
		}

		return $this->get_field_instance( $field_context->get_field_meta() )->get_template_variables( $field_value );
	}

	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		return $this->get_field_instance( $field_value->get_context()->get_field_meta() )->get_validation_template_variables( $field_value );
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return $this->get_field_instance( $field_meta )->is_with_field_wrapper( $layout_settings, $field_settings, $field_meta );
	}

	public function get_conditional_fields( Field_Meta $field_meta ): array {
		return $this->get_field_instance( $field_meta )->get_conditional_fields( $field_meta );
	}

	public function get_front_assets( Field_Settings $field_settings ): array {
		return $this->get_field_instance( $field_settings->get_field_meta() )->get_front_assets( $field_settings );
	}
}
