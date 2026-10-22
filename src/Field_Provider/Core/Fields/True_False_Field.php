<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Composed_Htmlable;

defined( 'ABSPATH' ) || exit;

class True_False_Field extends Markup_Field_Base {
	const STATE = 'state';

	public function compose_tags( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();
		$state_var     = $token_factory->variable( $field_id )->add_item_path( self::STATE );
		$item_class    = $this->get_item_class(
			'true-false--state--:',
			$markup_field_data->get_view_data(),
			$markup_field_data->get_field_data()
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

		return Html::tag(
			'div',
			array( 'class' => Composed_Htmlable::compose( $parts ) )
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$is_checked = in_array( $variable_field_data->get_value(), array( 1, '1', true ), true );

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
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		$is_checked = in_array( $variable_field_data->get_value(), array( 1, '1', true ), true );

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
