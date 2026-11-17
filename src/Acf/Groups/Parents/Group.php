<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Groups\Parents;

use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\AcfGroup;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

abstract class Group extends AcfGroup {
	const GROUP_NAME_PREFIX = 'local_acf_views_';

	// to keep back compatibility.
	const FIELD_NAME_PREFIX            = '';
	const TEXT_DOMAIN                  = 'acf-views';
	protected const FIELD_ID_PREFIX    = 'acf';
	protected const FIELD_ID_SEPARATOR = '-';

	public static function get_acf_field_id( string $field_name ): string {
		return implode(
			self::FIELD_ID_SEPARATOR,
			array( self::FIELD_ID_PREFIX, static::getAcfFieldName( $field_name ) )
		);
	}

	public static function resolve_field_name_from_id( string $field_id ): string {
		$parts  = explode( self::FIELD_ID_SEPARATOR, $field_id );
		$prefix = string( $parts, 0 );

		if ( self::FIELD_ID_PREFIX === $prefix ) {
			array_shift( $parts );
		}

		return implode( self::FIELD_ID_SEPARATOR, $parts );
	}

	protected static function convertCamelCaseToDashes( string $subject ): string {
		$subject = parent::convertCamelCaseToDashes( $subject );

		// for back compatibility.
		return str_replace( '_', '-', $subject );
	}
}
