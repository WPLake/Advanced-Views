<?php


declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Fields;

use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;

defined( 'ABSPATH' ) || exit;

class Template_Field_Data {
	private Layout_Settings $layout_settings;
	// item can be null (in case of repeater sub-field).
	private ?Item_Settings $item_settings;
	private Field_Settings $field_settings;
	private Field_Meta $field_meta;
	private Field_Markup $field_markup;
	private Markup_Field $markup_field;

	public function __construct(
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Field_Markup $field_markup,
		Markup_Field $markup_field
	) {
		$this->layout_settings = $layout_settings;
		$this->item_settings   = $item_settings;
		$this->field_settings  = $field_settings;
		$this->field_meta      = $field_meta;
		$this->field_markup    = $field_markup;
		$this->markup_field    = $markup_field;
	}

	public function get_view_data(): Layout_Settings {
		return $this->layout_settings;
	}

	public function set_view_data( Layout_Settings $layout_settings ): void {
		$this->layout_settings = $layout_settings;
	}

	public function get_item_data(): ?Item_Settings {
		return $this->item_settings;
	}

	public function set_item_data( ?Item_Settings $item_settings ): void {
		$this->item_settings = $item_settings;
	}

	public function get_field_data(): Field_Settings {
		return $this->field_settings;
	}

	public function set_field_data( Field_Settings $field_settings ): void {
		$this->field_settings = $field_settings;
	}

	public function get_field_markup(): Field_Markup {
		return $this->field_markup;
	}

	public function set_field_markup( Field_Markup $field_markup ): void {
		$this->field_markup = $field_markup;
	}

	public function get_field_meta(): Field_Meta {
		return $this->field_meta;
	}

	public function set_field_meta( Field_Meta $field_meta ): void {
		$this->field_meta = $field_meta;
	}

	public function get_field_instance(): Markup_Field {
		return $this->markup_field;
	}

	public function set_field_instance( Markup_Field $markup_field ): void {
		$this->markup_field = $markup_field;
	}
}
