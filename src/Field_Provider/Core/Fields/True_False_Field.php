<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use function Org\Wplake\Advanced_Views\Template\Html\compose_attribute;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class True_False_Field implements Markup_Field {
	use Markup_Field_Base;

	const STATE = 'state';

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$field_context = $markup_field_data->get_context();

		$token_factory = $markup_field_data->get_token_factory();
		$state_var     = $token_factory->variable( $field_id )->add_item_path( self::STATE );
		$item_class    = $this->get_item_class(
			'true-false--state--:',
			$field_context->get_layout_settings(),
			$field_context->get_field_settings()
		);

		// the state token goes in place of the ':' placeholder.
		$item_class_parts = explode( ':', $item_class );
		$parts            = array(
			$this->get_field_class( 'true-false', $markup_field_data ) . ' ' . array_shift( $item_class_parts ),
		);

		foreach ( $item_class_parts as $item_class_part ) {
			$parts[] = $state_var;
			$parts[] = $item_class_part;
		}

		$composed_class = compose_attribute( $parts );

		return array(
			tag( 'div' )
				->attributes( array( 'class' => $composed_class ) ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$is_checked = in_array( $field_value->get_value(), array( 1, '1', true ), true );

		$args = array(
			self::VALUE => $is_checked,
			self::STATE => $is_checked ?
				'checked' :
				'unchecked',
		);

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$is_checked = in_array( $field_value->get_value(), array( 1, '1', true ), true );

		$args = array(
			self::VALUE => $is_checked,
			self::STATE => $is_checked ?
				'checked' :
				'unchecked',
		);

		return $args;
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return $layout_settings->is_with_unnecessary_wrappers;
	}

	// mark that false value still is supported in markup.
	public function is_empty_value_supported_in_markup(): bool {
		return true;
	}
}
