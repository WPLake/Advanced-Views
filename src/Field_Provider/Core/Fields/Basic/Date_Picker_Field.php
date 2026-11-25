<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Basic;

use DateTime;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Date_Picker_Field implements Markup_Field {
	use Markup_Field_Base;

	const TIMESTAMP = 'timestamp';

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$value_var = $token_factory
			->variable( $field_id )
			->add_item_path( self::VALUE );

		return array( $value_var );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$args = array(
			self::VALUE     => '',
			self::TIMESTAMP => 0,
		);

		$date_time = $field_value->convert_value_to_date_time();

		if ( $date_time instanceof DateTime ) {
			$date_variables = $this->compose_date_variables( $field_value, $date_time );

			return array_merge( $args, $date_variables );
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$now            = new DateTime();
		$display_format = $field_value->get_context()->get_field_meta()->get_display_format();

		return array(
			self::VALUE     => $now->format( $display_format ),
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

	/**
	 * @return array<string, mixed>
	 */
	protected function compose_date_variables( Raw_Field_Value $field_value, DateTime $date_time ): array {
		$timestamp      = $date_time->getTimestamp();
		$display_format = $field_value->get_context()->get_field_meta()->get_display_format();

		// date_i18n() unlike the '$date->format($displayFormat)' supports different languages.
		$date = date_i18n( $display_format, $timestamp );

		return array(
			self::VALUE     => $date,
			self::TIMESTAMP => $timestamp,
		);
	}
}
