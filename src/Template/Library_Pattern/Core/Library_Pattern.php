<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

defined( 'ABSPATH' ) || exit;

interface Library_Pattern {
	const ORIGIN_LITE = 'lite';
	const ORIGIN_PRO  = 'pro';

	public function get_name(): string;

	public function get_auto_discover_name(): string;

	/**
	 * Where the pattern's JS files live (an ORIGIN_* constant).
	 */
	public function get_asset_origin(): string;

	/**
	 * @return string[] handles of the active front JS files (the file is js/front/{handle}.min.js)
	 */
	public function get_active_js_handles(): array;

	/**
	 * @return array<string,array{url:string,deps:string[]}> handle => an external script, deps are handles
	 */
	public function get_external_scripts(): array;

	/**
	 * @return array<string,array<string,array<mixed>>> handle => [ JS object name => value ]
	 */
	public function get_js_data(): array;

	/**
	 * @return string the final CSS code
	 */
	public function get_active_css(): string;
}
