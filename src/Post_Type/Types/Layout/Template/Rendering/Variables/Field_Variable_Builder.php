<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variables;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Instance;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Field_Context\Field_Context_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Field_Value\Field_Value_Resolver;

use function Org\Wplake\Advanced_Views\Utils\is_blank_value;

/**
 * Builds the field variables of a Layout instance: the loaded (or fake, for the validation) values with the defaults applied.
 */
final class Field_Variable_Builder {
	private Field_Value_Resolver $field_value_resolver;
	private Field_Context_Loader $field_context_loader;

	public function __construct( Field_Value_Resolver $field_value_resolver, Field_Context_Loader $field_context_loader ) {
		$this->field_value_resolver = $field_value_resolver;
		$this->field_context_loader = $field_context_loader;
	}

	/**
	 * @param array<string,mixed> $field_values Filled with the loaded field values
	 *
	 * @return array<string,mixed>
	 */
	public function get_fields_variables( Layout_Instance $layout, array &$field_values ): array {
		$twig_variables = array();

		$items = $layout->get_view_data()->items;

		foreach ( $items as $item ) {
			$field_meta  = $item->field->get_field_meta();
			$field_value = $layout->get_field_value( $item->field, $field_meta, $item );

			$field_variables = $this->get_field_variables(
				$layout,
				$item,
				$field_meta,
				$field_value,
				false,
				$field_values
			);
			$twig_variables  = array_merge( $twig_variables, $field_variables );
		}

		return $twig_variables;
	}

	/**
	 * @param array<string,mixed> $field_values Filled with the field values
	 *
	 * @return array<string,mixed>
	 */
	public function get_validation_fields_variables( Layout_Instance $layout, array &$field_values ): array {
		$twig_variables = array();

		$items = $layout->get_view_data()->items;

		foreach ( $items as $item ) {
			$field_meta = $item->field->get_field_meta();

			$field_variables = $this->get_field_variables(
				$layout,
				$item,
				$field_meta,
				null,
				true,
				$field_values
			);
			$twig_variables  = array_merge( $twig_variables, $field_variables );
		}

		return $twig_variables;
	}

	/**
	 * @param mixed $field_value
	 * @param array<string,mixed> $field_values Filled with the field value (with the defaults applied)
	 *
	 * @return array<string,mixed>
	 */
	protected function get_field_variables(
		Layout_Instance $layout,
		Item_Settings $item,
		Field_Meta $field_meta,
		$field_value,
		bool $is_for_validation,
		array &$field_values
	): array {
		$field_value = self::apply_default_values( $field_value, $item, $field_meta );

		$field_values[ $item->field->id ] = $field_value;

		return $this->get_template_args_for_variable( $layout, $item, $field_meta, $field_value, $is_for_validation );
	}

	/**
	 * @param mixed $field_value
	 *
	 * @return array<string, mixed>
	 */
	protected function get_template_args_for_variable(
		Layout_Instance $layout,
		Item_Settings $item_settings,
		Field_Meta $field_meta,
		$field_value,
		bool $is_for_validation
	): array {
		$field_context = $this->field_context_loader->load(
			$layout->get_view_data(),
			$item_settings,
			$item_settings->field,
			$field_meta
		);
		$twig_args     = null !== $field_context ?
			$this->field_value_resolver->resolve_value(
				$field_context,
				$layout,
				$layout->get_source(),
				$field_value,
				$is_for_validation
			) :
			array();

		$field_id   = $item_settings->field->get_template_field_id();
		$label_args = array(
			'label' => $item_settings->field->get_label_translation(),
		);
		$field_args = array_merge( $twig_args, $label_args );

		return array(
			$field_id => $field_args,
		);
	}

	/**
	 * @param mixed $field_value
	 *
	 * @return mixed
	 */
	protected static function apply_default_values( $field_value, Item_Settings $item, Field_Meta $field_meta ) {
		$is_native = $field_meta->is_native_type();

		$is_empty_value = is_blank_value( $field_value );

		// 1. default value from our plugin. Note: custom field types don't support default values
		if ( $is_empty_value && $is_native ) {
			$field_value = $item->field->default_value;
		}

		$is_empty_value = is_blank_value( $field_value );

		// 2. default value from ACF. Note: custom field types don't support default values
		if ( $is_empty_value && $is_native ) {
			$field_value = $field_meta->get_default_value();
		}

		return $field_value;
	}
}
