<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use WP_Term;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Taxonomy_Field extends List_Field {
	const LOOP_ITEM_NAME = 'term_item';
	const PARENT_ID      = 'parent_id';

	private Link_Field $link_field;

	public function __construct( Link_Field $link_field ) {
		$this->link_field = $link_field;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$layout_id = $field_value->get_context()->get_field_settings()->get_short_unique_acf_view_id();

		$parent_variables = parent::get_template_variables( $field_value );

		return array_merge(
			$parent_variables,
			array(
				self::VIEW_ID   => $layout_id,
				self::LAYOUT_ID => $layout_id,
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$layout_id = $field_value->get_context()->get_field_settings()->get_short_unique_acf_view_id();

		$parent_variables = parent::get_validation_template_variables( $field_value );

		return array_merge(
			$parent_variables,
			array(
				self::VIEW_ID   => $layout_id,
				self::LAYOUT_ID => $layout_id,
			)
		);
	}

	public function get_front_assets( Field_Settings $field_settings ): array {
		$parent_assets = parent::get_front_assets( $field_settings );

		return $this->get_link_front_assets( $parent_assets, $field_settings );
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		$parent_fields = parent::get_conditional_fields( $field_meta );

		return $this->get_link_conditional_fields( $parent_fields, $field_meta );
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		if ( $field_settings->has_external_layout() ) {
			return true;
		}

		return parent::is_with_field_wrapper( $layout_settings, $field_settings, $field_meta );
	}

	/**
	 * @return array{url: string, title: string}
	 */
	protected static function get_term_info( int $id ): array {
		$post_info = array(
			Link_Field::URL   => '',
			Link_Field::TITLE => '',
		);

		$term = get_term( $id );

		if ( $term instanceof WP_Term ) {
			$term_link = get_term_link( $term );

			return array(
				Link_Field::URL   => is_string( $term_link ) ? $term_link : '',
				// decode to avoid double encoding in Twig.
				Link_Field::TITLE => html_entity_decode( $term->name, ENT_QUOTES ),
			);
		}

		return $post_info;
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_internal_item_layout( string $item_id, Template_Field_Meta $markup_field_data ): array {
		$is_multiple           = $markup_field_data->get_context()->get_field_meta()->is_multiple();
		$is_with_field_wrapper = $is_multiple || $markup_field_data->is_with_field_wrapper();

		$markup_field_data->set_is_with_field_wrapper( $is_with_field_wrapper );

		return $this->link_field->compose_html( $item_id, $markup_field_data );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_external_item_layout( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		return $this->compose_external_layout_shortcode( $field_id, $markup_field_data, 'term' );
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_item_template_args( Raw_Field_Value $field_value ): array {
		$term_id = $field_value->get_value();
		$wp_term = is_numeric( $term_id ) ?
			get_term( (int) $term_id ) :
			null;

		$parent_id = $wp_term instanceof WP_Term ?
			$wp_term->parent :
			0;

		if ( $field_value->get_context()->get_field_settings()->has_external_layout() ) {
			return array(
				self::VALUE     => $term_id,
				self::PARENT_ID => $parent_id,
			);
		}

		$value = int( $term_id );

		$field_value->set_value( self::get_term_info( $value ) );

		$link_variables = $this->link_field->get_template_variables( $field_value );

		return array_merge(
			$link_variables,
			array(
				self::PARENT_ID => $parent_id,
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_validation_item_template_args( Raw_Field_Value $field_value ): array {
		if ( $field_value->get_context()->get_field_settings()->has_external_layout() ) {
			return array(
				self::VALUE     => 0,
				self::PARENT_ID => 0,
			);
		}

		$link_variables = $this->link_field->get_validation_template_variables( $field_value );

		return array_merge(
			$link_variables,
			array(
				self::PARENT_ID => 0,
			)
		);
	}
}
