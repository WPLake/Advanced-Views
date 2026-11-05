<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Pattern_Container;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

/**
 * Immutable, created by the Row_Context_Loader.
 */
final class Row_Context {
	private Layout_Settings $layout_settings;
	// item can be null (in case of repeater sub-field).
	private ?Item_Settings $item_settings;
	private Field_Settings $field_settings;
	private Field_Meta $field_meta;
	private ?Markup_Field $markup_field;
	private Token_Factory $token_factory;
	private Pattern_Container $pattern_container;
	private string $field_id;
	private string $row_type;
	private string $row_suffix;

	public function __construct(
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		?Markup_Field $markup_field,
		Token_Factory $token_factory,
		Pattern_Container $pattern_container,
		string $field_id,
		string $row_type,
		string $row_suffix
	) {
		$this->layout_settings   = $layout_settings;
		$this->item_settings     = $item_settings;
		$this->field_settings    = $field_settings;
		$this->field_meta        = $field_meta;
		$this->markup_field      = $markup_field;
		$this->token_factory     = $token_factory;
		$this->pattern_container = $pattern_container;
		$this->field_id          = $field_id;
		$this->row_type          = $row_type;
		$this->row_suffix        = $row_suffix;
	}

	public function with_row_type( string $row_type ): self {
		$context           = clone $this;
		$context->row_type = $row_type;

		return $context;
	}

	public function get_layout_settings(): Layout_Settings {
		return $this->layout_settings;
	}

	public function get_item_settings(): ?Item_Settings {
		return $this->item_settings;
	}

	public function get_field_settings(): Field_Settings {
		return $this->field_settings;
	}

	public function get_field_meta(): Field_Meta {
		return $this->field_meta;
	}

	public function get_markup_field(): ?Markup_Field {
		return $this->markup_field;
	}

	public function get_token_factory(): Token_Factory {
		return $this->token_factory;
	}

	public function get_pattern_container(): Pattern_Container {
		return $this->pattern_container;
	}

	public function get_field_id(): string {
		return $this->field_id;
	}

	public function get_row_type(): string {
		return $this->row_type;
	}

	public function get_row_suffix(): string {
		return $this->row_suffix;
	}
}
