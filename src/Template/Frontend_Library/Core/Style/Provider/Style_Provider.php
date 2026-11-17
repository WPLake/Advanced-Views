<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

interface Style_Provider {
	/**
	 * The gate: the methods below are called only when it's true.
	 */
	public function has_active_styles(): bool;

	/**
	 * Where the CSS files live: the assets root, relative to the plugin root (e.g. 'src/Assets/').
	 */
	public function get_style_origin(): string;

	/**
	 * @return string[] handles of the active front CSS files (the file is css/front/{handle}.min.css)
	 */
	public function get_style_handles(): array;

	/**
	 * [subject => replace], applied to the CSS files code.
	 * Used e.g. to patch wrong url pointers - 'url(../'.
	 *
	 * @return array<string,string>
	 */
	public function get_style_patches( Asset_Resolver $resolver ): array;

	/**
	 * The same rules format as Style_Pattern::get_css_rules(), but with the selector-keyed rules only:
	 * [selector => [property => value]]
	 *
	 * @return array<string, array<string, string>>
	 */
	public function get_inline_styles(): array;
}
