<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System;

defined( 'ABSPATH' ) || exit;

class Tailwind_Integrator {
	const MARKER = 'advanced-views:tailwind';

	private File_System $file_system;
	private Live_Reloader_Component $live_reloader_component;
	/**
	 * @var array<string,true>
	 */
	private array $css_rules;

	public function __construct( File_System $file_system, Live_Reloader_Component $live_reloader_component ) {
		$this->file_system             = $file_system;
		$this->live_reloader_component = $live_reloader_component;
		$this->css_rules               = array();
	}

	public function is_tailwind( string $css ): bool {
		return str_contains( $css, self::MARKER );
	}

	/**
	 * Empties the potentially large list of the already printed rules, as it's not needed anymore.
	 */
	public function reset(): self {
		$this->css_rules = array();

		return $this;
	}

	public function merge( string $tailwind_css ): string {
		$tailwind_css = Code_Minifier::minify_css( $tailwind_css );

		// 1. get all the media queries.
		preg_match_all( '/(@media[^{]*)\{((?:[^{}]*\{[^{}]*\})*[^{}]*)\}/', $tailwind_css, $media_queries, PREG_SET_ORDER );

		$media_rules = array();
		foreach ( $media_queries as $media_query ) {
			$media_condition = trim( $media_query[1] );
			$media_content   = trim( $media_query[2] );

			$media_rules[ $media_condition ] ??= '';
			$media_rules[ $media_condition ]  .= $media_content;
		}

		// 2. remove all the media queries from the primary css.
		$tailwind_css = (string) preg_replace( '/@media[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/', '', $tailwind_css );

		// 3. merge all the media queries.
		$condition_css = '';

		foreach ( $media_rules as $media_condition => $media_content ) {
			$unique_media_content = $this->get_unique_rules( $media_content, $media_condition );

			$condition_css .= strlen( $unique_media_content ) > 0 ?
				$media_condition . '{' . $unique_media_content . '}' :
			'';
		}

		$primary_css = $this->get_unique_rules( $tailwind_css );

		return $primary_css . $condition_css;
	}

	public function get_global_styles(): string {
		if ( $this->file_system->is_active() ) {
			$base_folder           = $this->file_system->get_target_base_folder();
			$tailwind_globals_file = sprintf( '%s/tailwind.css', $base_folder );
			$wp_filesystem         = $this->file_system->get_wp_filesystem();

			if ( $wp_filesystem->exists( $tailwind_globals_file ) ) {
				$tailwind_styles = (string) $wp_filesystem->get_contents( $tailwind_globals_file );

				return Code_Minifier::minify_css( $tailwind_styles );
			}
		}

		return '';
	}

	protected function get_unique_rules( string $css, string $prefix = '' ): string {
		$merged_css = '';

		/**
		 * Covers the following cases:
		 * 1. '.class{}',
		 * 2. 'h1{}',
		 * 3. '.class,h1{}',
		 * 4. ':host .class'.
		 */
		preg_match_all( '/([:.a-z]+[^{]*){([^}]+)}/', $css, $matches, PREG_SET_ORDER );

		$css_rules = array();

		foreach ( $matches as $match ) {
			$selector = trim( $match[1] );
			$rules    = trim( $match[2] );

			$global_selector = $prefix . $selector;

			if ( false === key_exists( $global_selector, $this->css_rules ) ) {
				$this->css_rules[ $global_selector ] = true;

				$css_rules[ $selector ] = $rules;
			}
		}

		$prefix_length              = strlen( $prefix );
		$is_important_rule_required = $prefix_length > 0 &&
									$this->live_reloader_component->is_active();

		foreach ( $css_rules as $selector => $rules ) {
			// tailwind @media rules require '!important' to make sure they work regardless of the position
			// (it's actual with the live reloading mode).
			if ( $is_important_rule_required ) {
				$rules  = str_replace( ';', '!important;', $rules );
				$rules .= '!important';
			}

			$merged_css .= $selector . '{' . $rules . '}';
		}

		return $merged_css;
	}
}
