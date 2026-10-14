<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Environment;

defined( 'ABSPATH' ) || exit;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\intOrNull;

abstract class Plugin_Tester {
	public static function get_logger_backtrace_limit(): ?int {
		$value = self::get_constant( 'ACF_VIEWS_LOGGER_BACKTRACE_LIMIT' );

		return intOrNull( $value );
	}

	public static function is_test_bootstrap(): bool {
		$value = self::get_constant( 'ACF_VIEWS_TEST_BOOTSTRAP' );

		return bool( $value );
	}

	/**
	 * @return mixed|null
	 */
	protected static function get_constant( string $name ) {
		if ( defined( $name ) ) {
			return constant( $name );
		}

		return null;
	}
}
