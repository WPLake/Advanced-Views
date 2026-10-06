<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Group;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable_Base;

defined( 'ABSPATH' ) || exit;

final class Acf_Utils {
	/**
	 * @param callable(array<string,mixed> $field): array<string,mixed> $get_overrides getter of the field args to merge
	 */
	public static function override_field_settings( string $field_name, callable $get_overrides ): void {
		$merge_overrides = function ( array $field ) use ( $get_overrides ): array {
			$overrides = $get_overrides( $field );

			return array_merge( $field, $overrides );
		};

		$hook_name = sprintf( 'acf/load_field/name=%s', $field_name );
		Hookable_Base::add_filter( $hook_name, $merge_overrides );
	}

	/**
	 * @param class-string<Group> $group_class
	 * @param array<string,callable(array<string,mixed> $field): array<string,string>> $choices_callbacks field constant => choices getter
	 */
	public static function bind_field_choices( string $group_class, array $choices_callbacks ): void {
		foreach ( $choices_callbacks as $field_constant => $get_choices ) {
			$field_name = $group_class::getAcfFieldName( $field_constant );

			$get_overrides = fn( array $field ) => array( 'choices' => $get_choices( $field ) );

			self::override_field_settings( $field_name, $get_overrides );
		}
	}
}
