<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Comment;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Comment_Author_Email_Field extends Markup_Field_Base {
	use Custom_Field;

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		return array(
			$token_factory
				->variable( $field_id )
			->add_item_path( self::VALUE ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$args = array(
			self::VALUE => $field_value->get_context()->get_field_settings()->default_value,
		);

		$comment = $this->get_comment( $field_value->get_value() );

		if ( null === $comment ) {
			return $args;
		}

		$email = get_comment_author_email( $comment );

		$args[ self::VALUE ] = '' !== $email ?
			$email :
			$args[ self::VALUE ];

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		return array(
			self::VALUE => 'rene@gmail.com',
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return true;
	}

	public function get_custom_field_wrapper_tag(): string {
		return 'p';
	}
}
