<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Library;

defined( 'ABSPATH' ) || exit;

interface JS_Library {
	/**
	 * Where the JS files live: the assets root, relative to the plugin root (e.g. 'src/Assets/').
	 */
	public function get_js_origin(): string;

	/**
	 * The gate: the methods below are called only when it's true.
	 */
	public function has_active_js(): bool;

	/**
	 * @return string[] handles of the active front JS files (the file is js/front/{handle}.min.js)
	 */
	public function get_active_js_handles(): array;

	/**
	 * @return array<string,array{url:string,deps:string[]}> handle => an external script, deps are handles
	 */
	public function get_external_scripts(): array;

	/**
	 * @return array<string, array<string, mixed[]>> handle => [ JS object name => value ]
	 */
	public function get_js_data(): array;
}
