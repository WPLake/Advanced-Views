<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use DateTime;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use Org\Wplake\Advanced_Views\Template\Html\Html;

defined( 'ABSPATH' ) || exit;

class Date_Picker_Field extends Markup_Field_Base {
	const TIMESTAMP = 'timestamp';

	public function compose_html( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		return Html::stack(
			$token_factory
				->variable( $field_id )
			->add_item_path( self::VALUE )
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			self::VALUE     => '',
			self::TIMESTAMP => 0,
		);

		$date_time = $variable_field_data->convert_value_to_date_time();

		if ( null === $date_time ) {
			return $args;
		}

		return array_merge(
			$args,
			array(
				// date_i18n() unlike the '$date->format($displayFormat)' supports different languages.
				self::VALUE     => date_i18n(
					$variable_field_data->get_field_meta()->get_display_format(),
					$date_time->getTimestamp()
				),
				self::TIMESTAMP => $date_time->getTimestamp(),
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		$now = new DateTime();

		return array(
			self::VALUE     => $now->format( $variable_field_data->get_field_meta()->get_display_format() ),
			self::TIMESTAMP => $now->getTimestamp(),
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return true;
	}

	public function get_custom_field_wrapper_tag(): string {
		return 'p';
	}
}
