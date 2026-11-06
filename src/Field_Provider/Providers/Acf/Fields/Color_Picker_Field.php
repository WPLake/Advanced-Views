<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

class Color_Picker_Field implements Markup_Field {
	use Markup_Field_Base;

	const RED   = 'red';
	const GREEN = 'green';
	const BLUE  = 'blue';
	const ALPHA = 'alpha';

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		if ( 'string' === $markup_field_data->get_context()->get_field_meta()->get_return_format() ) {
			$var = $token_factory->variable( $field_id )->add_item_path( self::VALUE );

			return array(
				$var,
			);
		}

		$parts      = array( 'red', 'green', 'blue', 'alpha' );
		$statements = array();

		foreach ( $parts as $part ) {
			$part_var     = $token_factory->variable( $field_id )
											->add_item_path( $part );
			$statements[] = $part_var->get_statement();
		}

		$rgba = sprintf( 'rgba(%s)', join( ';', $statements ) );

		return array( raw_html( $rgba ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

		$args = array(
			self::VALUE => '',
			self::RED   => '',
			self::GREEN => '',
			self::BLUE  => '',
			self::ALPHA => '',
		);

		$value = null;

		if ( 'string' === $field_context->get_field_meta()->get_return_format() &&
			is_string( $field_value->get_value() ) ) {
			$value = $field_value->get_value();
		} elseif ( is_array( $field_value->get_value() ) ) {
			$value = $field_value->get_value();
		}

		if ( null === $value ) {
			return $args;
		}

		if ( 'string' === $field_context->get_field_meta()->get_return_format() ) {
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

	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
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
