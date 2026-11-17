<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Group;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Interfaces\FieldInfoInterface;

defined( 'ABSPATH' ) || exit;

final class Field_Value_Guard {
	protected const KIND_CHOICE      = 'choice';
	protected const KIND_GROUP       = 'group';
	protected const KIND_GROUP_ARRAY = 'group_array';
	protected const KIND_PLAIN       = 'plain';
	protected const KIND_NONE        = 'none';

	/**
	 * A Pro option is kept only if it's already the stored one, otherwise the stored value (the default for a new item)
	 * is returned, so only new Pro values are blocked.
	 *
	 * @param mixed $origin_value
	 * @param array<int|string,mixed> $pro_choices
	 */
	public static function resolve_pro_choice( string $new_value, $origin_value, array $pro_choices ): string {
		if ( is_string( $origin_value ) ) {
			return self::revert_new_pro_choice(
				$new_value,
				$origin_value,
				$pro_choices
			);
		}

		return $new_value;
	}

	/**
	 * Reverts the Pro values of the new instance to the origin ones (the field defaults, if there is no origin).
	 */
	public static function reset_pro_fields( Group $new_instance, ?Group $origin_instance ): void {
		$fields_info = $new_instance::getFieldsInfo();

		foreach ( $fields_info as $field_info ) {
			self::reset_field(
				$new_instance,
				$origin_instance,
				$field_info
			);
		}
	}

	protected static function reset_field( Group $new_instance, ?Group $origin_instance, FieldInfoInterface $field_info ): void {
		$field_name   = $field_info->getName();
		$origin_value = self::get_origin_value( $field_info, $origin_instance );
		// @phpstan-ignore-next-line
		$new_value = $new_instance->{$field_name};
		$kind      = self::resolve_field_kind( $field_info, $new_value, $origin_value );

		$handlers = array(
			self::KIND_CHOICE      => fn() => self::reset_choice(
				$new_instance,
				$field_info,
				$origin_value
			),
			self::KIND_GROUP       => fn() => self::reset_pro_fields( $new_value, $origin_value instanceof Group ? $origin_value : null ),
			self::KIND_GROUP_ARRAY => fn() => self::reset_group_array( $new_value, $origin_value ),
			self::KIND_PLAIN       => fn() => self::reset_plain_value(
				$new_instance,
				$field_name,
				$origin_value
			),
		);

		if ( key_exists( $kind, $handlers ) ) {
			$handlers[ $kind ]();
		}
	}

	/**
	 * @param mixed $new_value
	 * @param mixed $origin_value
	 */
	protected static function resolve_field_kind( FieldInfoInterface $field_info, $new_value, $origin_value ): string {
		$arguments       = $field_info->getArguments();
		$is_pro          = (bool) ( $arguments['a-pro'] ?? false );
		$pro_choices     = self::get_pro_choices( $field_info );
		$is_string_value = is_string( $new_value );
		$is_group_array  = self::is_group_array( $new_value );

		// the order is the priority.
		$matches = array(
			self::KIND_CHOICE      => array() !== $pro_choices && $is_string_value,
			self::KIND_GROUP       => $new_value instanceof Group,
			self::KIND_GROUP_ARRAY => $is_group_array,
			self::KIND_PLAIN       => $is_pro && null !== $origin_value,
		);

		$kind = array_search( true, $matches, true );

		return is_string( $kind ) ?
			$kind :
			self::KIND_NONE;
	}

	/**
	 * @return array<int|string,mixed>
	 */
	protected static function get_pro_choices( FieldInfoInterface $field_info ): array {
		$arguments = $field_info->getArguments();

		return (array) ( $arguments['a-pro-choices'] ?? array() );
	}

	/**
	 * @return mixed
	 */
	protected static function get_origin_value( FieldInfoInterface $field_info, ?Group $origin_instance ) {
		if ( null !== $origin_instance ) {
			$field_name = $field_info->getName();

			// @phpstan-ignore-next-line
			return $origin_instance->{$field_name};
		}

		$arguments = $field_info->getArguments();

		return $arguments['default_value'] ?? null;
	}

	/**
	 * @param mixed $value
	 */
	protected static function is_group_array( $value ): bool {
		$is_array  = is_array( $value );
		$is_filled = $is_array && count( $value ) > 0;

		return $is_filled &&
				$value[0] instanceof Group;
	}

	/**
	 * @param array<int|string,mixed> $pro_choices
	 */
	protected static function revert_new_pro_choice( string $new_value, string $origin_value, array $pro_choices ): string {
		$is_changed    = $new_value !== $origin_value;
		$is_pro_choice = in_array(
			$new_value,
			$pro_choices,
			true
		);

		return $is_changed && $is_pro_choice ?
			$origin_value :
			$new_value;
	}

	/**
	 * @param mixed $origin_value
	 */
	protected static function reset_choice( Group $new_instance, FieldInfoInterface $field_info, $origin_value ): void {
		$field_name  = $field_info->getName();
		$pro_choices = self::get_pro_choices( $field_info );
		// @phpstan-ignore-next-line
		$new_value = $new_instance->{$field_name};

		// @phpstan-ignore-next-line
		$new_instance->{$field_name} = self::resolve_pro_choice(
			$new_value,
			$origin_value,
			$pro_choices
		);
	}

	/**
	 * @param Group[] $new_items
	 * @param mixed $origin_value
	 */
	protected static function reset_group_array( array $new_items, $origin_value ): void {
		$items_count = count( $new_items );

		for ( $i = 0; $i < $items_count; $i++ ) {
			/**
			 * @var array<int, Group|null> $origin_value
			 */
			self::reset_pro_fields( $new_items[ $i ], $origin_value[ $i ] ?? null );
		}
	}

	/**
	 * @param mixed $origin_value
	 */
	protected static function reset_plain_value( Group $new_instance, string $field_name, $origin_value ): void {
		// potential number string to int, so it doesn't cause fatal error
		// check exactly the field default initially, and only then origin.

		// @phpstan-ignore-next-line
		$is_int_field   = is_int( $new_instance->{$field_name} );
		$is_numeric     = is_numeric( $origin_value );
		$is_int_convert = $is_int_field && $is_numeric;

		$new_value = $is_int_convert ?
			(int) $origin_value :
			$origin_value;

		// @phpstan-ignore-next-line
		$new_instance->{$field_name} = $new_value;
	}
}
