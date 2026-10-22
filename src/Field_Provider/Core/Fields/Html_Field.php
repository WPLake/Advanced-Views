<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use function Org\Wplake\Advanced_Views\Template\Html\stack;

defined( 'ABSPATH' ) || exit;

class Html_Field extends Markup_Field_Base {
	const RAW_VALUE = 'raw_value';

	private bool $is_formatted_value_in_use;
	private bool $is_with_replace_new_line_to_br;

	/**
	 * @param bool $is_formatted_value_in_use Can be enabled for specific cases, e.g. ACF textarea field (to avoid adding 'p' tags)
	 * @param bool $is_with_replace_new_line_to_br Can be enabled for specific cases, e.g. ACF textarea field
	 */
	public function __construct(
		bool $is_formatted_value_in_use = true,
		bool $is_with_replace_new_line_to_br = false
	) {
		$this->is_formatted_value_in_use      = $is_formatted_value_in_use;
		$this->is_with_replace_new_line_to_br = $is_with_replace_new_line_to_br;
	}

	public function compose_html( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		return stack(
			fn() => $token_factory
				->variable( $field_id )
				->add_item_path( self::VALUE )
			->raw_display()
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$twig_args = array(
			self::VALUE     => '',
			self::RAW_VALUE => '',
		);

		// by default use the formatted value, as we need html (e.g. for oembed) instead of the pure value.
		$value = $this->is_formatted_value_in_use ?
			$variable_field_data->get_formatted_value() :
			$variable_field_data->get_value();

		$value = is_string( $value ) ||
				is_numeric( $value ) ?
			(string) $value :
			'';
		$value = $this->is_with_replace_new_line_to_br ?
			str_replace( "\n", '<br/>', $value ) :
			$value;

		return array_merge(
			$twig_args,
			array(
				self::VALUE     => $value,
				// always provide the raw value alongside with the current,
				// e.g. in oEmbed case, it allows to get the url instead of the iframe's html.
				self::RAW_VALUE => $variable_field_data->get_value(),
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			self::VALUE     => '1',
			self::RAW_VALUE => '1',
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return true;
	}
}
