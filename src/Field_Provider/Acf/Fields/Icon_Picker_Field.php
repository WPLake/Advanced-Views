<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Acf\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Template\Html\Tags_Renderer;
use Org\Wplake\Advanced_Views\Plugin\Utils\Safe_Array_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\Comparison_Token;

defined( 'ABSPATH' ) || exit;

final class Icon_Picker_Field extends Markup_Field_Base {
	const TYPE = 'type';

	use Safe_Array_Arguments;

	private Image_Field $image_field;

	public function __construct( Image_Field $image_field ) {
		$this->image_field = $image_field;
	}

	public function compose_tags( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$dashicons_branch     = $this->dashicons_branch( $field_id, $markup_field_data );
		$media_library_branch = $this->media_library_branch( $field_id, $markup_field_data );
		$custom_image_branch  = $this->custom_image_branch( $field_id, $markup_field_data );

		return array(
			$token_factory
				->if()
				->set_if_branch( $dashicons_branch )
				->set_elseif_branch( $media_library_branch )
				->set_else_branch( $custom_image_branch ),
		);
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

				$image_variables = $this->image_field->get_template_variables( $variable_field_data );
				$args            = array_merge( $args, $image_variables );
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

	protected function dashicons_branch( string $field_id, Template_Field_Data $markup_field_data ): IF_Branch {
		$token_factory = $markup_field_data->get_token_factory();

		$condition = self::type_comparison( 'dashicons', $field_id, $markup_field_data );
		$body      = $token_factory->html(
			fn() => $this->print_icon_markup( $field_id, $markup_field_data )
		);

		return $token_factory->if_branch()
			->set_condition( $condition )
			->set_body( $body );
	}

	protected function media_library_branch( string $field_id, Template_Field_Data $markup_field_data ): IF_Branch {
		$token_factory = $markup_field_data->get_token_factory();

		$condition    = self::type_comparison( 'media_library', $field_id, $markup_field_data );
		$body_printer = fn() => $this->print_icon_image_markup( $field_id, $markup_field_data );
		$body         = $token_factory->html( $body_printer );

		return $token_factory->if_branch()
			->set_condition( $condition )
			->set_body( $body );
	}

	protected function custom_image_branch( string $field_id, Template_Field_Data $markup_field_data ): IF_Branch {
		$token_factory = $markup_field_data->get_token_factory();

		$body = $token_factory->html(
			fn() => $this->print_custom_image_markup( $field_id, $markup_field_data )
		);

		return $token_factory->if_branch()
			->set_body( $body );
	}

	protected function print_icon_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$icon_var = $token_factory->variable( $field_id )->add_item_path( self::VALUE );

		Tags_Renderer::print_tag_group(
			array(
				function () use ( $token_factory, $markup_field_data ) {
					$token_factory->formatter()->new_line();
					$markup_field_data->increment_and_print_tabs();
				},
				array(
					'tag'   => 'i',
					'attrs' => array(
						'class' => array(
							$this->get_field_class( 'icon', $markup_field_data ),
							'dashicons',
							$icon_var,
						),
					),
				),
				function () use ( $token_factory, $markup_field_data ) {
					$token_factory->formatter()->new_line();
					$markup_field_data->decrement_and_print_tabs();
				},
			)
		);
	}

	protected function print_icon_image_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$token_factory->formatter()->new_line();
		$markup_field_data->increment_and_print_tabs();

		Tags_Renderer::print_tags( $this->image_field->compose_tags( $field_id, $markup_field_data ) );

		$token_factory->formatter()->new_line();
		$markup_field_data->decrement_and_print_tabs();
	}

	protected function print_custom_image_markup( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$src_var = $token_factory->variable( $field_id )->add_item_path( self::VALUE );

		Tags_Renderer::print_tag_group(
			array(
				function () use ( $token_factory, $markup_field_data ) {
					$token_factory->formatter()->new_line();
					$markup_field_data->increment_and_print_tabs();
				},
				array(
					'tag'   => 'img',
					'attrs' => array(
						'class'   => $this->get_field_class( 'icon', $markup_field_data ),
						'src'     => $src_var,
						'loading' => 'lazy',
						'alt'     => 'icon',
					),
				),
				function () use ( $token_factory, $markup_field_data ) {
					$token_factory->formatter()->new_line();
					$markup_field_data->decrement_and_print_tabs();
				},
			)
		);
	}

	protected static function type_comparison( string $type, string $field_id, Template_Field_Data $markup_field_data ): Comparison_Token {
		$token_factory = $markup_field_data->get_token_factory();
		$type_var      = $token_factory->variable( $field_id )
										->add_item_path( self::TYPE );
		$type_literal  = $token_factory->literal( $type );

		return $token_factory->comparison()
			->set_left_operand( $type_var )
			->set_comparison_equal()
			->set_right_operand( $type_literal );
	}
}
