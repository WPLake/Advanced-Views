<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Comment;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

class Comment_Author_Name_Link_Field extends Markup_Field_Base {
	use Custom_Field;

	private Link_Field $link_field;

	public function __construct( Link_Field $link_field ) {
		$this->link_field = $link_field;
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		return $this->link_field->compose_html( $field_id, $markup_field_data );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$comment = $this->get_comment( $field_value->get_value() );

		if ( null === $comment ) {
			$field_value->set_value( array() );

			return $this->link_field->get_template_variables( $field_value );
		}

		$author_name = get_comment_author( $comment );
		$author_url  = get_comment_author_url( $comment );

		$filed_args = array(
			Link_Field::URL   => $author_url,
			// avoid double escaping in Twig.
			Link_Field::TITLE => html_entity_decode( $author_name, ENT_QUOTES ),
		);

		$field_value->set_value( $filed_args );

		return $this->link_field->get_template_variables( $field_value );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		return $this->link_field->get_validation_template_variables( $field_value );
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return $layout_settings->is_with_unnecessary_wrappers ||
				$this->link_field->is_with_field_wrapper( $layout_settings, $field_settings, $field_meta );
	}
}
