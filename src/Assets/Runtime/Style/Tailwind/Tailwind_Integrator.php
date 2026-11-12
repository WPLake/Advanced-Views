<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;

defined( 'ABSPATH' ) || exit;

final class Tailwind_Integrator {
	const MARKER = 'advanced-views:tailwind';

	private Live_Reloader_Component $live_reloader_component;
	/**
	 * @var array<string,true>
	 */
	private array $css_rules;

	public function __construct( Live_Reloader_Component $live_reloader_component ) {
		$this->live_reloader_component = $live_reloader_component;
		$this->css_rules               = array();
	}

	public function is_tailwind( string $css ): bool {
		$marker_position = strpos( $css, self::MARKER );

		return is_int( $marker_position );
	}

	/**
	 * Empties the potentially large list of the already printed rules, as it's not needed anymore.
	 */
	public function reset(): self {
		$this->css_rules = array();

		return $this;
	}

	/**
	 * @param string $tailwind_css must be minified, the parsing relies on it.
	 */
	public function merge( string $tailwind_css ): string {
		$media_rules   = self::extract_media_rules( $tailwind_css );
		$primary_css   = self::strip_media_queries( $tailwind_css );
		$condition_css = $this->merge_media_rules( $media_rules );
		$unique_css    = $this->get_unique_rules( $primary_css );

		return $unique_css . $condition_css;
	}

	/**
	 * @param array<string,string> $media_rules
	 */
	protected function merge_media_rules( array $media_rules ): string {
		$condition_css = '';

		foreach ( $media_rules as $media_condition => $media_content ) {
			$unique_media_content = $this->get_unique_rules( $media_content, $media_condition );
			$is_filled            = strlen( $unique_media_content ) > 0;

			$condition_css .= $is_filled ?
				sprintf( '%s{%s}', $media_condition, $unique_media_content ) :
				'';
		}

		return $condition_css;
	}

	protected function get_unique_rules( string $css, string $prefix = '' ): string {
		$css_rules = $this->register_new_rules( $css, $prefix );

		$prefix_length              = strlen( $prefix );
		$is_important_rule_required = $prefix_length > 0 &&
									$this->live_reloader_component->is_active();
		$merged_css                 = '';

		foreach ( $css_rules as $selector => $rules ) {
			$rules = $is_important_rule_required ?
				self::mark_important( $rules ) :
				$rules;

			$merged_css .= sprintf( '%s{%s}', $selector, $rules );
		}

		return $merged_css;
	}

	/**
	 * Keeps only the rules that weren't printed yet, and remembers them.
	 *
	 * @return array<string,string> selector => rules
	 */
	protected function register_new_rules( string $css, string $prefix ): array {
		/**
		 * Covers the following cases:
		 * 1. '.class{}',
		 * 2. 'h1{}',
		 * 3. '.class,h1{}',
		 * 4. ':host .class'.
		 */
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

			$global_selector = $prefix . $selector;

			$is_new_rule = false === key_exists( $global_selector, $this->css_rules );

			if ( $is_new_rule ) {
				$this->css_rules[ $global_selector ] = true;

				$css_rules[ $selector ] = $rules;
			}
		}

		return $css_rules;
	}

	/**
	 * @return array<string,string> media condition => its rules
	 */
	protected static function extract_media_rules( string $tailwind_css ): array {
		preg_match_all(
			'/(@media[^{]*)\{((?:[^{}]*\{[^{}]*\})*[^{}]*)\}/',
			$tailwind_css,
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

	protected static function strip_media_queries( string $tailwind_css ): string {
		return (string) preg_replace(
			'/@media[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/',
			'',
			$tailwind_css
		);
	}

	/**
	 * Tailwind @media rules require '!important' to work regardless of the position
	 * (it's actual with the live reloading mode).
	 */
	protected static function mark_important( string $rules ): string {
		$important_rules = str_replace(
			';',
			'!important;',
			$rules
		);

		return sprintf( '%s!important', $important_rules );
	}
}
