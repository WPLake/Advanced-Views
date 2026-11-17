<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Field;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Field_Class_Decorator extends Actor_Base implements Actor {
	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	/**
	 * @template T of array<int|string,mixed>
	 *
	 * @param T $holder an ACF field or wrapper, both keep the classes in the 'class' key
	 *
	 * @return T
	 */
	// phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames -- 'class' is the ACF key name.
	public static function append_class( array $holder, string $class ): array {
		$current_class = string( $holder, 'class' );
		$has_class     = strlen( $current_class ) > 0;

		$holder['class'] = $has_class ?
			sprintf( '%s %s', $current_class, $class ) :
			$class;

		return $holder;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_filter( 'acf/prepare_field', array( $this, 'amend_field_classes' ) );
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	public function amend_field_classes( array $field ): array {
		$field = $this->add_deprecated_field_class( $field );
		$field = $this->add_custom_field_class( $field );

		return $field;
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	protected function add_deprecated_field_class( array $field ): array {
		if ( key_exists( 'a-deprecated', $field ) ) {
			$wrapper = arr( $field, 'wrapper' );

			$field['wrapper'] = self::append_class( $wrapper, 'acf-field--deprecated' );
		}

		return $field;
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	protected function add_custom_field_class( array $field ): array {
		$class_argument = 'a-html-class';

		if ( key_exists( $class_argument, $field ) ) {
			$wrapper      = arr( $field, 'wrapper' );
			$custom_class = string( $field, $class_argument );

			$field['wrapper'] = self::append_class( $wrapper, $custom_class );
		}

		return $field;
	}
}
