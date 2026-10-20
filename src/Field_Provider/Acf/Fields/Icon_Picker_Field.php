<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Acf\Fields;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\Comparison_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable\Variable_Token;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Plugin\Utils\Safe_Array_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;

defined( 'ABSPATH' ) || exit;

class Icon_Picker_Field extends Markup_Field_Base {
	const TYPE = 'type';

	use Safe_Array_Arguments;

	private Image_Field $image_field;

	public function __construct( Image_Field $image_field ) {
		$this->image_field = $image_field;
	}

	public function print_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$type_var = $token_factory->variable( $field_id )
									->add_item_path( self::TYPE );

		$token_factory
			->if()
			->new_if_branch( fn( IF_Branch $branch ) => $this->set_dashicons_branch( $branch, $type_var, $field_id, $markup_field_data ) )
			->new_elseif_branch( fn( IF_Branch $branch ) => $this->set_media_library_branch( $branch, $type_var, $field_id, $markup_field_data ) )
			->new_else_branch( fn( IF_Branch $branch ) => $this->set_custom_image_branch( $branch, $field_id, $markup_field_data ) )
			->display();
	}

	protected function set_dashicons_branch( IF_Branch $branch, Variable_Token $type_var, string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$branch
			->set_condition( $this->type_comparison( $type_var, 'dashicons', $markup_field_data ) )
			->set_body( $token_factory->html( fn() => $this->print_icon_markup( $field_id, $markup_field_data ) ) );
	}

	protected function set_media_library_branch( IF_Branch $branch, Variable_Token $type_var, string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$branch
			->set_condition( $this->type_comparison( $type_var, 'media_library', $markup_field_data ) )
			->set_body( $token_factory->html( fn() => $this->print_icon_image_markup( $field_id, $markup_field_data ) ) );
	}

	protected function set_custom_image_branch( IF_Branch $branch, string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$branch->set_body( $token_factory->html( fn() => $this->print_custom_image_markup( $field_id, $markup_field_data ) ) );
	}

	protected function type_comparison( Variable_Token $type_var, string $type, Template_Field_Data $markup_field_data ): Comparison_Token {
		$token_factory = $markup_field_data->get_token_factory();

		return $token_factory->comparison()
			->set_left_operand( $type_var )
			->set_comparison_equal()
			->set_right_operand( $token_factory->literal( $type ) );
	}

	protected function print_icon_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$token_factory->formatter()->new_line();
		$markup_field_data->increment_and_print_tabs();

		printf(
			'<i class="%s dashicons ',
			esc_html(
				$this->get_field_class(
					'icon',
					$markup_field_data
				)
			),
		);
		$token_factory->variable( $field_id )->add_item_path( self::VALUE )->display();

		echo '"></i>';

		$token_factory->formatter()->new_line();
		$markup_field_data->decrement_and_print_tabs();
	}

	protected function print_icon_image_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$token_factory->formatter()->new_line();
		$markup_field_data->increment_and_print_tabs();

		$this->image_field->print_markup( $field_id, $markup_field_data );

		$token_factory->formatter()->new_line();
		$markup_field_data->decrement_and_print_tabs();
	}

	protected function print_custom_image_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$token_factory->formatter()->new_line();
		$markup_field_data->increment_and_print_tabs();

		printf(
			'<img class="%s" src="',
			esc_html(
				$this->get_field_class(
					'icon',
					$markup_field_data
				)
			),
		);
		$token_factory->variable( $field_id )->add_item_path( self::VALUE )->display();

		echo '" loading="lazy" alt="icon">';

		$token_factory->formatter()->new_line();
		$markup_field_data->decrement_and_print_tabs();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			self::TYPE  => '',
			self::VALUE => '',
		);

		$value = $variable_field_data->get_value();

		if ( false === is_array( $value ) ) {
			return $args;
		}

		$args[ self::TYPE ] = $this->get_string_arg( 'type', $value );

		switch ( $args[ self::TYPE ] ) {
			case 'dashicons':
			case 'url':
				$args[ self::VALUE ] = $this->get_string_arg( 'value', $value );
				break;
			case 'media_library':
				$attachment_id = $this->get_string_arg( 'value', $value );

				$variable_field_data->set_value( $attachment_id );

				$args = array_merge( $args, $this->image_field->get_template_variables( $variable_field_data ) );
				break;
		}

		return $args;
	}

	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			self::TYPE  => 'dashicons',
			self::VALUE => 'dashicons-admin-generic',
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return false;
	}
}
