<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

class Link_Field implements Markup_Field {
	use Markup_Field_Base;

	const TARGET     = 'target';
	const TITLE      = 'title';
	const LINK_LABEL = 'linkLabel';
	// keys of the source link value.
	const URL = 'url';

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
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

		$link_content = $token_factory
			->comparison()
			->set_left_operand( $link_label_var )
			->set_comparison_empty()
			->set_right_operand( $title_var );

		return array(
			tag( 'a' )
				->attributes( $link_attributes )
				->content(
					$link_content
				),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

		$args = array(
			self::VALUE      => '',
			self::TARGET     => '_self',
			self::TITLE      => '',
			self::LINK_LABEL => $field_context->get_field_settings()->get_link_label_translation(),
		);

		$value = arr( $field_value->get_value() );

		if ( 0 === count( $value ) ) {
			return $args;
		}

		$target        = string( $value, self::TARGET );
		$is_target_set = strlen( $target ) > 0 || bool( $value, self::TARGET );

		$args[ self::VALUE ]  = string( $value, self::URL );
		$args[ self::TITLE ]  = string( $value, self::TITLE );
		$args[ self::TARGET ] = $field_context->get_field_settings()->is_link_target_blank || $is_target_set ?
			'_blank' :
			'_self';

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		return array(
			self::VALUE      => 'https://wordpress.org/',
			self::TARGET     => '_self',
			self::TITLE      => 'wordpress.org',
			self::LINK_LABEL => $field_value->get_context()->get_field_settings()->get_link_label_translation(),
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
		return array(
			Field_Settings::FIELD_LINK_LABEL,
			Field_Settings::FIELD_IS_LINK_TARGET_BLANK,
		);
	}
}
