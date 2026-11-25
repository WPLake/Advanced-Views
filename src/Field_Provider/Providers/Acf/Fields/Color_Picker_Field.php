<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Color_Picker_Field implements Markup_Field {
	use Markup_Field_Base;

	const RED   = 'red';
	const GREEN = 'green';
	const BLUE  = 'blue';
	const ALPHA = 'alpha';

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();
		$return_format = $markup_field_data->get_context()->get_field_meta()->get_return_format();

		if ( 'string' === $return_format ) {
			$var = $token_factory
				->variable( $field_id )
				->add_item_path( self::VALUE );

			return array(
				$var,
			);
		}

		$statements = self::get_channel_statements( $field_id, $markup_field_data );
		$channels   = join( ';', $statements );
		$rgba       = sprintf( 'rgba(%s)', $channels );

		return array( raw_html( $rgba ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context    = $field_value->get_context();
		$return_format    = $field_context->get_field_meta()->get_return_format();
		$is_string_format = 'string' === $return_format;
		$raw_value        = $field_value->get_value();
		$is_valid         = ( $is_string_format && is_string( $raw_value ) ) || is_array( $raw_value );

		$args = array(
			self::VALUE => '',
			self::RED   => '',
			self::GREEN => '',
			self::BLUE  => '',
			self::ALPHA => '',
		);

		if ( $is_valid ) {
			return $is_string_format ?
				self::with_string_value( $args, $raw_value ) :
				self::with_channels_value( $args, arr( $raw_value ) );
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

	/**
	 * @return string[]
	 */
	protected static function get_channel_statements( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$channels = array(
			self::RED,
			self::GREEN,
			self::BLUE,
			self::ALPHA,
		);

		$statements = array();

		foreach ( $channels as $channel ) {
			$channel_var  = $token_factory
				->variable( $field_id )
				->add_item_path( $channel );
			$statements[] = $channel_var->get_statement();
		}

		return $statements;
	}

	/**
	 * @param array<string, mixed> $args
	 * @param mixed $value
	 *
	 * @return array<string, mixed>
	 */
	protected static function with_string_value( array $args, $value ): array {
		$args[ self::VALUE ] = $value;

		return $args;
	}

	/**
	 * @param array<string, mixed> $args
	 * @param array<string|int, mixed> $value
	 *
	 * @return array<string, mixed>
	 */
	protected static function with_channels_value( array $args, array $value ): array {
		$red = string( $value, self::RED );

		// value is just bool, as 'red' can be zero, but still be a value.
		$args[ self::VALUE ] = (bool) $red;
		$args[ self::RED ]   = $red;
		$args[ self::GREEN ] = string( $value, self::GREEN );
		$args[ self::BLUE ]  = string( $value, self::BLUE );
		$args[ self::ALPHA ] = string( $value, self::ALPHA );

		return $args;
	}
}
