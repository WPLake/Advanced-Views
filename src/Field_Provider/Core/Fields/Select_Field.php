<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Meta\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Meta\Raw_Field_Value;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Select_Field extends List_Field {
	const LOOP_ITEM_NAME = 'choice_item';
	const TITLE          = 'title';
	const CHOICES        = 'choices';

	/**
	 * @return Html_Node[]
	 */
	protected function compose_item_markup( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$twig_name = $markup_field_data->get_field_meta()->is_multiple() ?
			static::LOOP_ITEM_NAME :
			$field_id;

		$markup_field_data->set_is_with_field_wrapper(
			$markup_field_data->get_field_meta()->is_multiple() ||
			$markup_field_data->is_with_field_wrapper()
		);

		$token_factory  = $markup_field_data->get_token_factory();
		$div_attributes = array( 'class' => $this->get_field_class( 'choice', $markup_field_data ) );

		$title_var = $token_factory->variable( $twig_name )
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
	protected function get_item_template_args( Raw_Field_Value $variable_field_data ): array {
		$value = is_string( $variable_field_data->get_value() ) ?
			$variable_field_data->get_value() :
			'';

		return array(
			self::TITLE => $variable_field_data->get_field_meta()->get_choices()[ $value ] ?? '',
			self::VALUE => $value,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $variable_field_data ): array {
		return array_merge(
			parent::get_template_variables( $variable_field_data ),
			array(
				self::CHOICES => $variable_field_data->get_field_meta()->get_choices(),
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_validation_item_template_args( Raw_Field_Value $variable_field_data ): array {
		return array(
			self::TITLE => 'Option',
			self::VALUE => 'option',
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $variable_field_data ): array {
		return array_merge(
			parent::get_validation_template_variables( $variable_field_data ),
			array(
				self::CHOICES => array(
					'option' => 'Option',
				),
			)
		);
	}
}
