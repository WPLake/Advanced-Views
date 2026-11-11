<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Css\Library;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

interface CSS_Library {
	/**
	 * The gate: the methods below are called only when it's true.
	 */
	public function has_active_css(): bool;

	/**
	 * Where the CSS files live: the assets root, relative to the plugin root (e.g. 'src/Assets/').
	 */
	public function get_css_origin(): string;

	/**
	 * @return string[] handles of the active front CSS files (the file is css/front/{handle}.min.css)
	 */
	public function get_css_handles(): array;

	/**
	 * [subject => replace], applied to the CSS files code.
	 * Used e.g. to patch wrong url pointers - 'url(../'.
	 *
	 * @return array<string,string>
	 */
	public function get_css_patches( Asset_Resolver $resolver ): array;

	public function get_inline_css(): string;
}
