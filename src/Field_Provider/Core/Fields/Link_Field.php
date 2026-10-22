<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

class Link_Field extends Markup_Field_Base {
	const TARGET     = 'target';
	const TITLE      = 'title';
	const LINK_LABEL = 'linkLabel';
	// keys of the source link value.
	const URL = 'url';

	public function compose_tags( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$target_var     = $token_factory->variable( $field_id )
										->add_item_path( self::TARGET );
		$href_var       = $token_factory->variable( $field_id )
										->add_item_path( self::VALUE );
		$link_label_var = $token_factory->variable( $field_id )
											->add_item_path( self::LINK_LABEL );
		$title_var      = $token_factory->variable( $field_id )
									->add_item_path( self::TITLE );

		$link_attributes = array(
			'target' => $target_var,
			'class'  => $this->get_field_class( 'link', $markup_field_data ),
			'href'   => $href_var,
		);

		return Html::wrap(
			Html::tag(
				'a',
				$link_attributes
			),
			function () use ( $token_factory, $markup_field_data, $link_label_var, $title_var ) {
				$token_factory->formatter()
								->new_line();
				$markup_field_data->increment_and_print_tabs();

				$token_factory
					->comparison()
					->set_left_operand( $link_label_var )
					->set_comparison_empty()
					->set_right_operand( $title_var )
					->display();

				$token_factory->formatter()
								->new_line();
				$markup_field_data->decrement_and_print_tabs();
			}
		);
	}

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			self::VALUE      => '',
			self::TARGET     => '_self',
			self::TITLE      => '',
			self::LINK_LABEL => $variable_field_data->get_field_data()->get_link_label_translation(),
		);

		$value = arr( $variable_field_data->get_value() );

		if ( 0 === count( $value ) ) {
			return $args;
		}

		$target        = string( $value, self::TARGET );
		$is_target_set = strlen( $target ) > 0 || bool( $value, self::TARGET );

		$args[ self::VALUE ]  = string( $value, self::URL );
		$args[ self::TITLE ]  = string( $value, self::TITLE );
		$args[ self::TARGET ] = $variable_field_data->get_field_data()->is_link_target_blank || $is_target_set ?
			'_blank' :
			'_self';

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			self::VALUE      => 'https://wordpress.org/',
			self::TARGET     => '_self',
			self::TITLE      => 'wordpress.org',
			self::LINK_LABEL => $variable_field_data->get_field_data()->get_link_label_translation(),
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return $layout_settings->is_with_unnecessary_wrappers;
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		return array_merge(
			parent::get_conditional_fields( $field_meta ),
			array(
				Field_Settings::FIELD_LINK_LABEL,
				Field_Settings::FIELD_IS_LINK_TARGET_BLANK,
			)
		);
	}
}
