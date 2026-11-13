<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils;

defined( 'ABSPATH' ) || exit;

final class Code_Minifier {
	public static function minify_css( string $code ): string {
		return self::minify_common( $code );
	}

	public static function minify_js( string $code ): string {
		$code         = self::minify_common( $code );
		$replacements = array(
			' =' => '=',
			'= ' => '=',
			' ?' => '?',
			'? ' => '?',
		);

		return self::replace_all( $code, $replacements );
	}

	public static function strip_comments( string $code ): string {
		// remove all multiline comments.
		$code_without_comments = preg_replace( '|\/\*[\s\S]+\*\/|U', '', $code );
		$code                  = $code_without_comments ?? $code;

		// remove all single line comments.
		// \s at the begin is used to make sure url's aren't affected, e.g. 'url(http://example.com)' in CSS.
		$code_without_comments = preg_replace( '|[\s]+\/\/(.?)+\n|', '', $code );

		return $code_without_comments ?? $code;
	}

	protected static function minify_common( string $code ): string {
		$code = self::strip_comments( $code );

		// remove unnecessary spaces.
		$code = str_replace( array( "\t", "\n", "\r" ), '', $code );

		// replace multiple spaces with one.
		$code_without_extra_spaces = preg_replace( '|\s+|', ' ', $code );
		$code                      = $code_without_extra_spaces ?? $code;

		$replacements = array(
			': ' => ':',
			'; ' => ';',
			' {' => '{',
			'{ ' => '{',
			' }' => '}',
			'} ' => '}',
		);

		return self::replace_all( $code, $replacements );
	}

	/**
	 * @param array<string,string> $replacements search => replace, applied one by one in the given order
	 */
	protected static function replace_all( string $code, array $replacements ): string {
		foreach ( $replacements as $search => $replace ) {
			$code = str_replace( $search, $replace, $code );
		}

		return $code;
	}
}
