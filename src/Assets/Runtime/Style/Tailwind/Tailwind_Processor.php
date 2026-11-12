<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind;

use Org\Wplake\Advanced_Views\Plugin\Utils\Code_Minifier;

defined( 'ABSPATH' ) || exit;

/**
 * The page-wide Tailwind flow: the assets are processed first, then the globals are available.
 */
final class Tailwind_Processor {
	const MARKER = 'advanced-views:tailwind';

	private Tailwind_Rule_Merger $rule_merger;
	private Tailwind_Globals_Reader $globals_reader;
	private bool $is_in_use;

	public function __construct( Tailwind_Rule_Merger $rule_merger, Tailwind_Globals_Reader $globals_reader ) {
		$this->rule_merger    = $rule_merger;
		$this->globals_reader = $globals_reader;
		$this->is_in_use      = false;
	}

	/**
	 * @return string minified CSS, for a Tailwind asset also without the rules that were already printed
	 */
	public function process( string $css ): string {
		// the marker is a comment, so the check must precede the minification.
		$is_tailwind  = self::is_tailwind( $css );
		$minified_css = Code_Minifier::minify_css( $css );

		if ( $is_tailwind ) {
			$this->is_in_use = true;

			return $this->rule_merger->merge( $minified_css );
		}

		return $minified_css;
	}

	/**
	 * @return string minified global styles, empty when no Tailwind asset was processed
	 */
	public function get_globals(): string {
		if ( $this->is_in_use ) {
			$global_styles = $this->globals_reader->get_global_styles();

			return Code_Minifier::minify_css( $global_styles );
		}

		return '';
	}

	/**
	 * Drops the page-wide state, which is no longer needed once the styles are composed.
	 */
	public function reset(): self {
		$this->is_in_use = false;

		$this->rule_merger->reset();

		return $this;
	}

	protected static function is_tailwind( string $css ): bool {
		$marker_position = strpos( $css, self::MARKER );

		return is_int( $marker_position );
	}
}
