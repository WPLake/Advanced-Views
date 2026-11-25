<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields;

use WP_Post;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Links\Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\List_Field;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Raw_Field_Value;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Page_Link_Field extends List_Field {
	const LOOP_ITEM_NAME = 'link_item';

	private Link_Field $link_field;

	public function __construct( Link_Field $link_field ) {
		$this->link_field = $link_field;
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

	/**
	 * @return array<string, string>
	 */
	protected function get_item_template_args( Raw_Field_Value $field_value ): array {
		$id_or_url = self::get_id_or_url( $field_value->get_value() );
		$post_info = self::get_post_info( $id_or_url );

		$field_value->set_value( $post_info );

		return $this->link_field->get_template_variables( $field_value );
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_validation_item_template_args( Raw_Field_Value $field_value ): array {
		return $this->link_field->get_validation_template_variables( $field_value );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_item_markup( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$markup_field_data->set_is_with_field_wrapper(
			$markup_field_data->get_context()->get_field_meta()->is_multiple() ||
			$markup_field_data->is_with_field_wrapper()
		);

		return $this->link_field->compose_html( $item_id, $markup_field_data );
	}

	/**
	 * @param mixed $value
	 */
	protected static function get_id_or_url( $value ): string {
		$is_scalar = is_string( $value ) || is_numeric( $value );

		return $is_scalar ?
			(string) $value :
			'';
	}

	/**
	 * @return array{url: string, title: string}
	 */
	protected static function get_post_info( string $id_or_url ): array {
		$post = self::find_post( $id_or_url );

		if ( $post instanceof WP_Post ) {
			$title     = get_the_title( $post );
			$permalink = (string) get_permalink( $post->ID );

			return array(
				Link_Field::URL   => $permalink,
				// avoid double encoding in Twig.
				Link_Field::TITLE => html_entity_decode( $title, ENT_QUOTES ),
			);
		}

		return array(
			Link_Field::URL   => '',
			Link_Field::TITLE => '',
		);
	}

	protected static function find_post( string $id_or_url ): ?WP_Post {
		if ( is_numeric( $id_or_url ) ) {
			return get_post( (int) $id_or_url );
		}

		$site_url  = get_site_url();
		$post_slug = str_replace( $site_url, '', $id_or_url );
		$post_slug = trim( $post_slug, '/' );

		return get_page_by_path(
			$post_slug,
			OBJECT,
			array(
				'post',
				'page',
			)
		);
	}
}
