<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind;

defined( 'ABSPATH' ) || exit;

/**
 * The Tailwind styles of the current page: the assets are merged first, then the globals are available.
 */
final class Tailwind_Page_Styles {
	private Tailwind_Merger $rule_merger;
	private Tailwind_Globals $global_styles;
	private bool $is_in_use;

	public function __construct( Tailwind_Merger $rule_merger, Tailwind_Globals $global_styles ) {
		$this->rule_merger   = $rule_merger;
		$this->global_styles = $global_styles;
		$this->is_in_use     = false;
	}

	/**
	 * @param string $tailwind_css must be minified, the parser relies on it.
	 *
	 * @return string the CSS without the rules that were already printed
	 */
	public function merge_asset( string $tailwind_css ): string {
		$this->is_in_use = true;

		return $this->rule_merger->merge( $tailwind_css );
	}

	/**
	 * @return string raw global styles, empty when no Tailwind asset was merged
	 */
	public function get_globals(): string {
		return $this->is_in_use ?
			$this->global_styles->get_global_styles() :
			'';
	}

	/**
	 * Drops the page-wide state, which is no longer needed once the styles are composed.
	 */
	public function reset(): self {
		$this->is_in_use = false;

		$this->rule_merger->reset();

		return $this;
	}
}
