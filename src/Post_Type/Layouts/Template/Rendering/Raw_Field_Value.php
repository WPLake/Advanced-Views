<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering;

defined( 'ABSPATH' ) || exit;

use DateTime;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Field_Context\Field_Context;

final class Raw_Field_Value {
	private Field_Context $context;
	/**
	 * In case of repeater field, the formatted value isn't available directly
	 *
	 * @var mixed
	 */
	private $formatted_value;
	private bool $is_set_formatted_value;

	private Source $source;
	/**
	 * @var mixed $value
	 */
	private $value;
	private Layout $layout;
	private Field_Value_Resolver $field_value_resolver;

	public function __construct(
		Field_Context $context,
		Layout $layout,
		Source $source,
		Field_Value_Resolver $field_value_resolver
	) {
		$this->context                = $context;
		$this->layout                 = $layout;
		$this->source                 = $source;
		$this->field_value_resolver        = $field_value_resolver;
		$this->formatted_value        = null;
		$this->is_set_formatted_value = false;

		$this->value = null;
	}

	public function get_context(): Field_Context {
		return $this->context;
	}

	/**
	 * @param mixed $formatted_value
	 */
	public function set_formatted_value( $formatted_value ): void {
		$this->is_set_formatted_value = true;

		$this->formatted_value = $formatted_value;
	}

	/**
	 * @return mixed
	 */
	public function get_formatted_value() {
		// in case of repeater field, the formatted value isn't available directly.
		if ( $this->is_set_formatted_value ) {
			return $this->formatted_value;
		}

		// get the formatted value on fly (as it's used for some fields only, and shouldn't be called for all fields).
		$field_settings = $this->context->get_field_settings();
		$field_meta     = $this->context->get_field_meta();
		$item_settings  = $this->context->get_item_settings();

		return $this->layout->get_field_value( $field_settings, $field_meta, $item_settings, true );
	}

	public function convert_value_to_date_time(): ?DateTime {
		if ( false === is_string( $this->value ) ) {
			return null;
		}

		$field_meta = $this->context->get_field_meta();

		return $this->layout->convert_string_to_date_time( $field_meta, $this->value );
	}

	/**
	 * @return mixed $value
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function set_value( $value ): void {
		$this->value = $value;
	}

	public function get_resolver(): Field_Value_Resolver {
		return $this->field_value_resolver;
	}

	public function get_source(): Source {
		return $this->source;
	}

	public function get_view(): Layout {
		return $this->layout;
	}
}
