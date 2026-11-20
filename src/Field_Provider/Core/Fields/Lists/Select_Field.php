<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Select_Field extends List_Field {
	const LOOP_ITEM_NAME = 'choice_item';
	const TITLE          = 'title';
	const CHOICES        = 'choices';

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$parent_variables = parent::get_template_variables( $field_value );
		$choices          = $field_value->get_context()->get_field_meta()->get_choices();

		return array_merge(
			$parent_variables,
			array( self::CHOICES => $choices )
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$parent_variables = parent::get_validation_template_variables( $field_value );

		return array_merge(
			$parent_variables,
			array(
				self::CHOICES => array(
					'option' => 'Option',
				),
			)
		);
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_item_markup( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$field_context = $markup_field_data->get_context();

		$is_multiple = $field_context->get_field_meta()->is_multiple();

		$twig_name = $is_multiple ?
			static::LOOP_ITEM_NAME :
			$field_id;

		$is_with_field_wrapper = $is_multiple || $markup_field_data->is_with_field_wrapper();

		$markup_field_data->set_is_with_field_wrapper( $is_with_field_wrapper );

		$token_factory  = $markup_field_data->get_token_factory();
		$field_class    = $this->get_field_class( 'choice', $markup_field_data );
		$div_attributes = array( 'class' => $field_class );

		$title_var = $token_factory
			->variable( $twig_name )
			->add_item_path( self::TITLE );

		$tags = tag( 'div' )
			->attributes( $div_attributes )
			->content(
				$title_var
			);

		return array( $tags );
	}

	/**
	 * @return array<string, string>
	 */
	protected function get_item_template_args( Raw_Field_Value $field_value ): array {
		$raw_value = $field_value->get_value();
		$value     = is_string( $raw_value ) ?
			$raw_value :
			'';
		$choices   = $field_value->get_context()->get_field_meta()->get_choices();

		return array(
			self::TITLE => $choices[ $value ] ?? '',
			self::VALUE => $value,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_validation_item_template_args( Raw_Field_Value $field_value ): array {
		return array(
			self::TITLE => 'Option',
			self::VALUE => 'option',
		);
	}
}
