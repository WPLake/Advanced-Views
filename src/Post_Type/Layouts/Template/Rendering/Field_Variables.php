<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field_Template;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;

class Field_Variables {
	private Field_Provider_Cluster $provider_cluster;
	private Field_Template $field_template;

	public function __construct( Field_Provider_Cluster $provider_cluster, Field_Template $field_template ) {
		$this->provider_cluster = $provider_cluster;
		$this->field_template   = $field_template;
	}

	/**
	 * @param mixed $field_value
	 * @param mixed $formatted_value In repeater, formatted value must be passed directly
	 *
	 * @return mixed[]
	 */
	public function get_field_variables(
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Layout $layout,
		Source $source,
		$field_value,
		bool $is_for_validation = false,
		$formatted_value = null
	): array {
		$markup_field = $this->provider_cluster->get_markup_field_instance(
			$this->provider_cluster->get_vendor_name_by_key( $field_settings->key ),
			$field_meta->get_type()
		);

		if ( null === $markup_field ) {
			return array();
		}

		$variable_field_data = new Variable_Field_Data(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$this->field_template,
			$layout,
			$source,
			$markup_field,
			$this
		);

		$variable_field_data->set_value( $field_value );

		if ( null !== $formatted_value ) {
			$variable_field_data->set_formatted_value( $formatted_value );
		}

		$field_data = ! $is_for_validation ?
			$markup_field->get_template_variables( $variable_field_data ) :
			$markup_field->get_validation_template_variables( $variable_field_data );

		return $this->apply_field_data_filter( $field_data, $field_meta, $layout_settings->get_unique_id( true ) );
	}

	/**
	 * @param array<string,mixed> $field_data
	 *
	 * @return array<string|int,mixed>
	 */
	protected function apply_field_data_filter(
		array $field_data,
		Field_Meta $field_meta,
		string $short_unique_view_id
	): array {
		$field_data = Plugin::apply_filters(
			array(
				'advanced_views/layout/field_data',
				'acf_views/view/field_data',
			),
			$field_data,
			$field_meta,
			$short_unique_view_id
		);

		if ( $field_meta->is_native_type() ) {
			$field_data = Plugin::apply_filters(
				array(
					sprintf( 'advanced_views/layout/field_data/type=%s', $field_meta->get_type() ),
					sprintf( 'acf_views/view/field_data/type=%s', $field_meta->get_type() ),
				),
				$field_data,
				$field_meta,
				$short_unique_view_id
			);
		}

		$field_data = Plugin::apply_filters(
			array(
				sprintf( 'advanced_views/layout/field_data/name=%s', $field_meta->get_name() ),
				sprintf( 'acf_views/view/field_data/name=%s', $field_meta->get_name() ),
			),
			$field_data,
			$field_meta,
			$short_unique_view_id
		);

		$field_data = Plugin::apply_filters(
			array(
				sprintf( 'advanced_views/layout/field_data/layout_id=%s', $short_unique_view_id ),
				sprintf( 'acf_views/view/field_data/view_id=%s', $short_unique_view_id ),
			),
			$field_data,
			$field_meta,
			$short_unique_view_id
		);

		return arr( $field_data );
	}
}
