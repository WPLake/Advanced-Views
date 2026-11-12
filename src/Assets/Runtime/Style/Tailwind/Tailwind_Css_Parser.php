<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind;

defined( 'ABSPATH' ) || exit;

/**
 * The CSS must be minified, the parsing relies on it.
 */
final class Tailwind_Css_Parser {
	/**
	 * @return array<string,string> media condition => its rules
	 */
	public static function parse_media_rules( string $css ): array {
		preg_match_all(
			'/(@media[^{]*)\{((?:[^{}]*\{[^{}]*\})*[^{}]*)\}/',
			$css,
			$media_queries,
			PREG_SET_ORDER
		);

		$media_rules = array();

		foreach ( $media_queries as $media_query ) {
			$media_condition = trim( $media_query[1] );
			$media_content   = trim( $media_query[2] );

			$media_rules[ $media_condition ] ??= '';
			$media_rules[ $media_condition ]  .= $media_content;
		}

		return $media_rules;
	}

	public static function strip_media_queries( string $css ): string {
		return (string) preg_replace(
			'/@media[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/',
			'',
			$css
		);
	}

	/**
	 * Covers the following cases:
	 * 1. '.class{}',
	 * 2. 'h1{}',
	 * 3. '.class,h1{}',
	 * 4. ':host .class'.
	 *
	 * @return array<string,string> selector => rules, the first one wins for a repeated selector
	 */
	public static function parse_rules( string $css ): array {
		preg_match_all(
			'/([:.a-z]+[^{]*){([^}]+)}/',
			$css,
			$matches,
			PREG_SET_ORDER
		);

		$css_rules = array();

		foreach ( $matches as $match ) {
			$selector = trim( $match[1] );
			$rules    = trim( $match[2] );

			$css_rules[ $selector ] ??= $rules;
		}

		return $css_rules;
	}
}
