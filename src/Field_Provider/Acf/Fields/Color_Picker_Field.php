<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Acf\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

class Color_Picker_Field extends Markup_Field_Base {
	const RED   = 'red';
	const GREEN = 'green';
	const BLUE  = 'blue';
	const ALPHA = 'alpha';

	public function print_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		if ( 'string' === $markup_field_data->get_field_meta()->get_return_format() ) {
			$var = $token_factory->variable( $field_id )->add_item_path( self::VALUE );
			$var->to_display()->print_token();

			return;
		}

		$parts       = array( 'red', 'green', 'blue', 'alpha' );
		$items_count = count( $parts );

		echo 'rgba(';

		for ( $i = 0;$i < $items_count;$i++ ) {
			if ( $i > 0 ) {
				echo ';';
			}

			$token_factory->variable( $field_id )->add_item_path( $parts[ $i ] )->to_display()->print_token();
		}

		echo ')';
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			self::VALUE => '',
			self::RED   => '',
			self::GREEN => '',
			self::BLUE  => '',
			self::ALPHA => '',
		);

		$value = null;

		if ( 'string' === $variable_field_data->get_field_meta()->get_return_format() &&
			is_string( $variable_field_data->get_value() ) ) {
			$value = $variable_field_data->get_value();
		} elseif ( is_array( $variable_field_data->get_value() ) ) {
			$value = $variable_field_data->get_value();
		}

		if ( null === $value ) {
			return $args;
		}

		if ( 'string' === $variable_field_data->get_field_meta()->get_return_format() ) {
			$args[ self::VALUE ] = $value;
		} else {
			$red = string( $value, 'red' );
			// value is just bool, as 'red' can be zero, but still be a value.
			$args[ self::VALUE ] = (bool) $red;
			$args[ self::RED ]   = $red;
			$args[ self::GREEN ] = string( $value, 'green' );
			$args[ self::BLUE ]  = string( $value, 'blue' );
			$args[ self::ALPHA ] = string( $value, 'alpha' );
		}

		return $args;
	}

	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			self::VALUE => true,
			self::RED   => '1',
			self::GREEN => '1',
			self::BLUE  => '1',
			self::ALPHA => '1',
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
