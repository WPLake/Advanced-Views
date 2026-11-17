<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Field;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Field_Pro_Marker extends Actor_Base implements Actor {
	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_filter( 'acf/prepare_field', array( $this, 'amend_pro_field' ) );
		self::add_filter(
			'acf/field_wrapper_attributes',
			array( $this, 'add_class_to_admin_pro_field_classes' ),
			10,
			2
		);
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	public function amend_pro_field( array $field ): array {
		$is_locked = $this->is_locked_pro_field( $field );

		if ( $is_locked ) {
			$field = $this->amend_pro_field_label_and_instruction( $field );
		}

		return $this->amend_pro_choices_labels( $field );
	}

	/**
	 * @param array<string,mixed> $wrapper
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	public function add_class_to_admin_pro_field_classes( array $wrapper, array $field ): array {
		if ( $this->is_locked_pro_field( $field ) ) {
			$wrapper = Field_Class_Decorator::append_class( $wrapper, 'acf-views-pro' );
		}

		return $wrapper;
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	protected function amend_pro_field_label_and_instruction( array $field ): array {
		$type         = $field['type'] ?? '';
		$instructions = self::get_instructions( $field );

		$field['label']       ??= '';
		$field['instructions'] = $instructions;

		if ( 'tab' === $type ) {
			return Field_Class_Decorator::append_class( $field, 'acf-views-tab__pro' );
		}

		$field['label'] = $this->get_pro_label( string( $field, 'label' ) );

		return $field;
	}

	protected function get_pro_label( string $label ): string {
		$is_pro_version = $this->plugin->is_pro_version();
		$action_label   = $is_pro_version ?
			esc_html__( 'Unlock Pro', 'acf-views' ) :
			esc_html__( 'Upgrade to Pro', 'acf-views' );

		// labels do not support HTML, so we can't use a link.
		return sprintf(
			'%s (%s)',
			esc_html( $label ),
			$action_label
		);
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	protected function amend_pro_choices_labels( array $field ): array {
		$pro_choices = arr( $field, 'a-pro-choices' );
		$choices     = arr( $field, 'choices' );

		$has_choices = array() !== $pro_choices && array() !== $choices;

		if ( $has_choices ) {
			$field['choices'] = $this->get_labeled_choices( $choices, $pro_choices );
		}

		return $field;
	}

	/**
	 * @param array<string|int,mixed> $choices
	 * @param array<int|string,mixed> $pro_choices
	 *
	 * @return array<string|int,mixed>
	 */
	protected function get_labeled_choices( array $choices, array $pro_choices ): array {
		$pro_keys = self::get_existing_pro_keys( $choices, $pro_choices );

		foreach ( $pro_keys as $pro_key ) {
			$choice_label = string( $choices, $pro_key );

			$choices[ $pro_key ] = $this->plugin->get_pro_field_label( $choice_label );
		}

		return $choices;
	}

	/**
	 * @param array<string,mixed> $field
	 */
	protected function is_locked_pro_field( array $field ): bool {
		$has_pro_flag = key_exists( 'a-pro', $field );

		return $has_pro_flag &&
				$this->plugin->is_pro_field_locked();
	}

	/**
	 * @param array<string,mixed> $field
	 */
	protected static function get_instructions( array $field ): string {
		$has_instructions = key_exists( 'instructions', $field ) &&
							is_string( $field['instructions'] );

		return $has_instructions ?
			$field['instructions'] :
			'';
	}

	/**
	 * @param array<string|int,mixed> $choices
	 * @param array<int|string,mixed> $pro_choices
	 *
	 * @return string[]
	 */
	protected static function get_existing_pro_keys( array $choices, array $pro_choices ): array {
		$string_keys = array_filter( $pro_choices, 'is_string' );
		$all_keys    = array_keys( $choices );
		$existing    = array_intersect( $string_keys, $all_keys );

		return array_values( $existing );
	}
}
