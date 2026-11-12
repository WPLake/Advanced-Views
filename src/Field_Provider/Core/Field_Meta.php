<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

defined( 'ABSPATH' ) || exit;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

class Field_Meta {
	private string $vendor_name;
	private string $field_id;
	private string $name;
	private string $label;
	private string $identifier;
	private bool $is_pro_only;
	private string $type;
	private string $return_format;
	/**
	 * @var array<string, string>
	 */
	private array $choices;
	private bool $is_field_exist;
	private string $display_format;
	private bool $is_multiple;
	private bool $is_repeater;
	private bool $is_group;
	private ?Field_Meta $field_meta;
	/**
	 * @var mixed
	 */
	private $default_value;
	/**
	 * @var array<string, mixed>
	 */
	private array $custom_args;
	private int $zoom;
	private string $center_lat;
	private string $center_lng;
	private bool $is_ui_only;
	private bool $is_native_type;

	public function __construct( string $vendor_name, string $field_id ) {
		$this->vendor_name    = $vendor_name;
		$this->field_id       = $field_id;
		$this->name           = '';
		$this->label          = '';
		$this->identifier     = '';
		$this->is_pro_only    = false;
		$this->type           = '';
		$this->return_format  = '';
		$this->choices        = array();
		$this->is_field_exist = false;
		$this->display_format = '';
		$this->is_multiple    = false;
		$this->is_repeater    = false;
		$this->is_group       = false;
		$this->field_meta     = null;
		$this->default_value  = '';
		$this->custom_args    = array();
		$this->zoom           = 0;
		$this->center_lat     = '';
		$this->center_lng     = '';
		$this->is_ui_only     = false;
		$this->is_native_type = false;
	}

	public function is_field_exist(): bool {
		return $this->is_field_exist;
	}

	public function get_field_id(): string {
		return $this->field_id;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_identifier(): string {
		return $this->identifier;
	}

	public function is_pro_only(): bool {
		return $this->is_pro_only;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_type(): string {
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function get_custom_arg( string $arg_name ) {
		return $this->custom_args[ $arg_name ] ?? null;
	}

	/**
	 * @param mixed $arg_value
	 */
	public function set_custom_arg( string $arg_name, $arg_value ): self {
		$this->custom_args[ $arg_name ] = $arg_value;

		return $this;
	}

	public function unset_custom_arg( string $arg_name ): self {
		if ( key_exists( $arg_name, $this->custom_args ) ) {
			unset( $this->custom_args[ $arg_name ] );
		}

		return $this;
	}

	public function get_return_format(): string {
		return $this->return_format;
	}

	public function get_display_format(): string {
		return $this->display_format;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_choices(): array {
		return $this->choices;
	}

	public function is_multiple(): bool {
		return $this->is_multiple;
	}

	public function is_repeater(): bool {
		return $this->is_repeater;
	}

	public function is_group(): bool {
		return $this->is_group;
	}

	public function get_self_repeatable_meta(): ?Field_Meta {
		return $this->field_meta;
	}

	/**
	 * @return mixed
	 */
	public function get_default_value() {
		return $this->default_value;
	}

	public function get_zoom(): int {
		return $this->zoom;
	}

	public function get_center_lat(): string {
		return $this->center_lat;
	}

	public function get_center_lng(): string {
		return $this->center_lng;
	}

	public function get_vendor_name(): string {
		return $this->vendor_name;
	}

	public function is_ui_only(): bool {
		return $this->is_ui_only;
	}

	public function is_native_type(): bool {
		return $this->is_native_type;
	}

	// setters.

	public function set_is_field_exist( bool $is_field_exist ): self {
		$this->is_field_exist = $is_field_exist;

		return $this;
	}

	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function set_label( string $label ): self {
		$this->label = $label;

		return $this;
	}

	/**
	 * Vendor-native name, used instead of the label for the language-independent identifiers.
	 */
	public function set_identifier( string $identifier ): self {
		$this->identifier = $identifier;

		return $this;
	}

	public function set_is_pro_only( bool $is_pro_only ): self {
		$this->is_pro_only = $is_pro_only;

		return $this;
	}

	public function set_type( string $type ): self {
		$this->type = $type;

		return $this;
	}

	public function set_return_format( string $return_format ): self {
		$this->return_format = $return_format;

		return $this;
	}

	public function set_display_format( string $display_format ): self {
		$this->display_format = $display_format;

		return $this;
	}

	/**
	 * @param mixed[] $choices
	 */
	public function set_choices( array $choices ): self {
		foreach ( $choices as $key => $value ) {
			$is_unsupported = false === is_string( $value ) && false === is_numeric( $value );

			if ( $is_unsupported ) {
				continue;
			}

			$choice_key = string( $key );

			$this->choices[ $choice_key ] = string( $value );
		}

		return $this;
	}

	public function set_is_multiple( bool $is_multiple ): self {
		$this->is_multiple = $is_multiple;

		return $this;
	}

	public function set_is_repeater( bool $is_repeater ): self {
		$this->is_repeater = $is_repeater;

		return $this;
	}

	public function set_is_group( bool $is_group ): self {
		$this->is_group = $is_group;

		return $this;
	}

	public function set_self_repeatable_meta( ?Field_Meta $field_meta ): self {
		$this->field_meta = $field_meta;

		return $this;
	}

	/**
	 * @param mixed $default_value
	 */
	public function set_default_value( $default_value ): self {
		$this->default_value = $default_value;

		return $this;
	}

	public function set_zoom( int $zoom ): self {
		$this->zoom = $zoom;

		return $this;
	}

	public function set_center_lat( string $center_lat ): self {
		$this->center_lat = $center_lat;

		return $this;
	}

	public function set_center_lng( string $center_lng ): self {
		$this->center_lng = $center_lng;

		return $this;
	}

	public function set_is_ui_only( bool $is_ui_only ): self {
		$this->is_ui_only = $is_ui_only;

		return $this;
	}

	public function set_is_native_type( bool $is_native_type ): self {
		$this->is_native_type = $is_native_type;

		return $this;
	}
}
