<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
abstract class List_Field implements Markup_Field {
	use Markup_Field_Base;

	const LOOP_ITEM_NAME    = 'item';
	const OPTIONS_DELIMITER = 'options_delimiter';

	/**
	 * @return array<string, mixed>
	 */
	abstract protected function get_item_template_args( Raw_Field_Value $field_value ): array;

	/**
	 * @return array<string, mixed>
	 */
	abstract protected function get_validation_item_template_args( Raw_Field_Value $field_value ): array;

	// separate method, so it can be overridden in the child classes.

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function get_value( Field_Meta $field_meta, $value ) {
		if ( $field_meta->is_multiple() ) {
			if ( is_array( $value ) ) {
				return $value;
			}

			return array();
		}

		if ( is_string( $value ) ||
			is_numeric( $value ) ||
			is_bool( $value ) ) {
			return $value;
		}

		return '';
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		if ( $markup_field_data->get_context()->get_field_meta()->is_multiple() ) {
			return array( $this->item_loop( $field_id, $markup_field_data ) );
		}

		return $this->compose_inner_item( $field_id, $markup_field_data );
	}

	protected function item_loop( string $field_id, Template_Field_Meta $markup_field_data ): Loop_Token {
		$token_factory = $markup_field_data->get_token_factory();

		$source_var = $token_factory->variable( $field_id )
										->add_item_path( self::VALUE );
		$item_var   = $token_factory->variable( static::LOOP_ITEM_NAME );
		$loop       = $token_factory->loop()
			->set_source_variable( $source_var )
			->set_item_variable( $item_var );

		$index_var = $loop->get_index_variable();
		$loop_body = $this->compose_loop_body( $index_var, $field_id, $markup_field_data );

		return $loop->set_body( $loop_body );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_loop_body( Variable_Token $index_var, string $field_id, Template_Field_Meta $markup_field_data ): array {
		$inner_item       = $this->compose_inner_item( $field_id, $markup_field_data );
		$is_delimiter_set = strlen( $markup_field_data->get_context()->get_field_settings()->options_delimiter ) > 0;

		if ( $is_delimiter_set ) {
			$token_factory    = $markup_field_data->get_token_factory();
			$delimiter_branch = $this->delimiter_branch( $index_var, $field_id, $markup_field_data );

			$delimiter_if = $token_factory->if()
											->set_if_branch( $delimiter_branch );

			return array_merge( array( $delimiter_if ), $inner_item );
		}

		return $inner_item;
	}

	protected function delimiter_branch( Variable_Token $index_var, string $field_id, Template_Field_Meta $markup_field_data ): IF_Branch {
		$token_factory = $markup_field_data->get_token_factory();

		$secondary_element_comparison = $token_factory->comparison()
			->set_left_operand( $index_var )
			->set_comparison_greater()
			->set_right_operand( $token_factory->literal( 0 ) );

		$body = $this->compose_item_delimiter( $field_id, $markup_field_data );

		return $token_factory->if_branch()
			->set_condition( $secondary_element_comparison )
			->set_body( $body );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_item_delimiter( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$field_context = $markup_field_data->get_context();

		$token_factory = $markup_field_data->get_token_factory();

		$item_class = $this->get_item_class(
			'delimiter',
			$field_context->get_layout_settings(),
			$field_context->get_field_settings()
		);

		$delimiter_var = $token_factory->variable( $field_id )
										->add_item_path( self::OPTIONS_DELIMITER );

		$delimiter_tag = tag( 'span' )
			->attributes( array( 'class' => $item_class ) )
			->content( $delimiter_var );

		return array( $delimiter_tag );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_inner_item( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$item_id = $markup_field_data->get_context()->get_field_meta()->is_multiple() ?
			static::LOOP_ITEM_NAME :
			$field_id;

		return $this->compose_item( $field_id, $item_id, $markup_field_data );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

		$args = array(
			self::VALUE => array(),
		);

		if ( $field_context->get_field_meta()->is_multiple() ) {
			$args[ self::OPTIONS_DELIMITER ] = $field_context->get_field_settings()->options_delimiter;
		}

		$value = $this->get_value( $field_context->get_field_meta(), $field_value->get_value() );

		if ( array() === $value ||
			'' === $value ) {
			// it's a single item, so merge, not assign to the 'value' key.
			if ( ! $field_context->get_field_meta()->is_multiple() ) {
				$field_value->set_value( null );

				$args = array_merge(
					$args,
					$this->get_item_template_args( $field_value )
				);
			}

			return $args;
		}

		if ( $field_context->get_field_meta()->is_multiple() ) {
			$value = (array) $value;

			foreach ( $value as $item ) {
				$field_value->set_value( $item );

				$args[ self::VALUE ][] = $this->get_item_template_args( $field_value );
			}
		} else {
			$field_value->set_value( $value );

			// it's a single item, so merge, not assign to the 'value' key.
			$args = array_merge(
				$args,
				$this->get_item_template_args( $field_value )
			);
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

		$args = array(
			self::VALUE => array(),
		);

		if ( $field_context->get_field_meta()->is_multiple() ) {
			$args[ self::OPTIONS_DELIMITER ] = $field_context->get_field_settings()->options_delimiter;
		}

		$item_args = $this->get_validation_item_template_args( $field_value );
		$item      = array();

		if ( $field_context->get_field_meta()->is_multiple() ) {
			$item[] = $item_args;

			return array_merge(
				$args,
				array(
					self::VALUE => $item,
				)
			);
		}

		$item = $item_args;

		return array_merge( $args, $item );
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return $layout_settings->is_with_unnecessary_wrappers ||
				$field_meta->is_multiple();
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		$conditional_fields = $field_meta->is_multiple() ?
			array(
				Field_Settings::FIELD_OPTIONS_DELIMITER,
			) :
			array();

		return $conditional_fields;
	}
}
