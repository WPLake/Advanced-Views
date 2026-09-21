<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Fields\Markup_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Fields\Variable_Field_Data;

class Mb_Map_Field extends Markup_Field_Base {
	public function print_markup( string $field_id, Markup_Field_Data $markup_field_data ): void {
		$var = $markup_field_data->get_token_factory()->variable( $field_id )
								->add_item_path( 'value' );

		$markup_field_data->get_token_factory()->to_echo( $var )
							->set_is_raw( true )
							->print();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			'value' => $variable_field_data->get_formatted_value(),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			'value' => 'some <strong>html</strong>',
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
		return array_merge(
			parent::get_conditional_fields( $field_meta ),
			array(
				Field_Settings::FIELD_MAP_MARKER_ICON,
				Field_Settings::FIELD_MAP_MARKER_ICON_TITLE,
			)
		);
	}
}
