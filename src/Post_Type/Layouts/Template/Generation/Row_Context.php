<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Pattern_Container;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

final class Row_Context {
	private Layout_Settings $layout_settings;
	private Field_Settings $field_settings;
	private Field_Meta $field_meta;
	private Token_Factory $token_factory;
	private Pattern_Container $pattern_container;
	// item can be null (in case of repeater sub-field).
	private ?Item_Settings $item_settings = null;
	private ?Markup_Field $markup_field   = null;
	private string $field_id              = '';
	private string $row_type              = '';
	private string $row_suffix            = '';

	public function __construct(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Token_Factory $token_factory
	) {
		$this->layout_settings   = $layout_settings;
		$this->field_settings    = $field_settings;
		$this->field_meta        = $field_settings->get_field_meta();
		$this->token_factory     = $token_factory;
		$this->pattern_container = new Pattern_Container( array() );
	}

	public function set_field_meta( Field_Meta $field_meta ): self {
		$this->field_meta = $field_meta;

		return $this;
	}

	public function set_item_settings( ?Item_Settings $item_settings ): self {
		$this->item_settings = $item_settings;

		return $this;
	}

	public function set_markup_field( ?Markup_Field $markup_field ): self {
		$this->markup_field = $markup_field;

		return $this;
	}

	public function set_field_id( string $field_id ): self {
		$this->field_id = $field_id;

		return $this;
	}

	public function set_row_type( string $row_type ): self {
		$this->row_type = $row_type;

		return $this;
	}

	public function set_row_suffix( string $row_suffix ): self {
		$this->row_suffix = $row_suffix;

		return $this;
	}

	/**
	 * @param Template_Pattern[] $patterns
	 */
	public function set_patterns( array $patterns ): self {
		$this->pattern_container = new Pattern_Container( $patterns );

		return $this;
	}

	public function clone(): self {
		return clone $this;
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

	public function get_field_name_class(): string {
		$bem_name = $this->layout_settings->get_bem_name();

		return sprintf(
			'%s__%s%s',
			$bem_name,
			$this->field_settings->id,
			$this->row_suffix
		);
	}
}
