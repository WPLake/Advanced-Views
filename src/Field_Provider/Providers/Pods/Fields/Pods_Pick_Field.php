<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\File_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\Post_Object_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\Select_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\Taxonomy_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\User_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

defined( 'ABSPATH' ) || exit;

final class Pods_Pick_Field implements Markup_Field {
	use Markup_Field_Base;

	private Select_Field $select_field;
	private User_Field $user_field;
	private Post_Object_Field $post_object_field;
	private Taxonomy_Field $taxonomy_field;
	private File_Field $file_field;

	public function __construct(
		Select_Field $select_field,
		User_Field $user_field,
		Post_Object_Field $post_object_field,
		Taxonomy_Field $taxonomy_field,
		File_Field $file_field
	) {
		$this->select_field      = $select_field;
		$this->user_field        = $user_field;
		$this->post_object_field = $post_object_field;
		$this->taxonomy_field    = $taxonomy_field;
		$this->file_field        = $file_field;
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$field_context  = $markup_field_data->get_context();
		$field_meta     = $field_context->get_field_meta();
		$field_instance = $this->get_field_instance( $field_meta );

		return $field_instance->compose_html( $field_id, $markup_field_data );
	}

	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context  = $field_value->get_context();
		$field_meta     = $field_context->get_field_meta();
		$field_instance = $this->get_field_instance( $field_meta );
		$is_multiple    = $field_meta->is_multiple();
		$origin_value   = $field_value->get_value();

		$new_value = self::get_normalized_value( $field_instance, $origin_value, $is_multiple );
		$field_value->set_value( $new_value );

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

		// comment & nav_menu aren't supported (atm there is no single instance for them).
		$instances = array(
			'post_type' => $this->post_object_field,
			'taxonomy'  => $this->taxonomy_field,
			'user'      => $this->user_field,
			'media'     => $this->file_field,
		);

		if ( key_exists( $return_format, $instances ) ) {
			return $instances[ $return_format ];
		}

		return $this->select_field;
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected static function get_normalized_value( Markup_Field $markup_field, $value, bool $is_multiple ) {
		if ( $is_multiple ) {
			return self::get_instance_values( $markup_field, $value );
		}

		return self::get_instance_value( $markup_field, $value );
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed[]
	 */
	protected static function get_instance_values( Markup_Field $markup_field, $value ): array {
		if ( is_array( $value ) ) {
			return array_map(
				fn( $item ) => self::get_instance_value( $markup_field, $item ),
				$value
			);
		}

		return array();
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected static function get_instance_value( Markup_Field $markup_field, $value ) {
		$is_object_field = $markup_field instanceof Post_Object_Field ||
							$markup_field instanceof User_Field ||
							$markup_field instanceof File_Field;

		if ( $is_object_field ) {
			return self::get_object_id( $value );
		}

		if ( $markup_field instanceof Taxonomy_Field ) {
			return is_array( $value ) ?
				int( $value, 'term_id' ) :
				0;
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 */
	protected static function get_object_id( $value ): int {
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
