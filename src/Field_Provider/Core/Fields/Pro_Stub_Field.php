<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Fields\Markup_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Fields\Variable_Field_Data;

defined( 'ABSPATH' ) || exit;

class Pro_Stub_Field extends Markup_Field_Base {
	public function print_markup( string $field_id, Markup_Field_Data $markup_field_data ): void {
		$message = __( 'This field is available in the Pro version only', 'acf-views' );

		$markup_field_data->get_token_factory()
							->comment( $message )
							->print();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			'value' => '',
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			'value' => '',
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
