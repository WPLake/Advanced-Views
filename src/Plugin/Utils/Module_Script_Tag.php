<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Our own built JS bundles are real ES modules (they `import` their shared chunks directly - the browser resolves
 * those on its own, so no extra wp_enqueue_script() per chunk is needed), but wp_enqueue_script()'s own $args has
 * no 'type' key for that. This marks specific handles so their <script> tag gets type="module", while leaving
 * their wp_enqueue_script() dependency/version/footer wiring untouched.
 */
abstract class Module_Script_Tag {
	/**
	 * @var array<string, true>
	 */
	private static array $handles = array();

	public static function mark( string $handle ): void {
		if ( array() === self::$handles ) {
			add_filter( 'script_loader_tag', array( self::class, 'filter_tag' ), 10, 2 );
		}

		self::$handles[ $handle ] = true;
	}

	public static function filter_tag( string $tag, string $handle ): string {
		if ( false === key_exists( $handle, self::$handles ) ) {
			return $tag;
		}

		return str_replace( '<script ', '<script type="module" ', $tag );
	}
}
