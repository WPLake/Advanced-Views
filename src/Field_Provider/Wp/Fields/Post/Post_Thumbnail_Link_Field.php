<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Post;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Printer;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Post_Thumbnail_Link_Field extends Markup_Field_Base {
	const HREF = 'href';

	use Custom_Field;

	protected Image_Field $image_field;

	public function __construct( Image_Field $image_field, Web_Component_Printer $printer ) {
		parent::__construct( $printer );

		$this->image_field = $image_field;
	}

	public function compose_html( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$target_var = $token_factory->variable( $field_id )
										->add_item_path( Link_Field::TARGET );
		$href_var   = $token_factory->variable( $field_id )
										->add_item_path( self::HREF );

		$link_attributes = array(
			'target' => $target_var,
			'class'  => $this->get_field_class( 'link', $markup_field_data ),
			'href'   => $href_var,
		);

		$markup_field_data->set_is_with_field_wrapper( true );

		return array(
			tag( 'a' )
				->attributes( $link_attributes )
				->content( ...$this->image_field->compose_html( $field_id, $markup_field_data ) ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			Link_Field::TARGET => $variable_field_data->get_field_data()->is_link_target_blank ?
				'_blank' :
				'_self',
			self::HREF         => '',
		);

		$post = $this->get_post( $variable_field_data->get_value() );

		if ( null === $post ) {
			$variable_field_data->set_value( 0 );

			return array_merge(
				$args,
				$this->image_field->get_template_variables( $variable_field_data )
			);
		}

		// @phpstan-ignore-next-line
		$args[ self::HREF ] = (string) get_the_permalink( $post );
		$image_id           = (int) get_post_thumbnail_id( $post );

		$variable_field_data->set_value( $image_id );

		return array_merge(
			$args,
			$this->image_field->get_template_variables( $variable_field_data )
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			Link_Field::TARGET => $variable_field_data->get_field_data()->is_link_target_blank ?
				'_blank' :
				'_self',
			self::HREF         => '',
		);

		$link_args = $this->image_field->get_validation_template_variables( $variable_field_data );

		return array_merge( $args, $link_args );
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
				Field_Settings::FIELD_IMAGE_SIZE,
				Field_Settings::FIELD_IS_LINK_TARGET_BLANK,
			)
		);
	}
}
