<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\CSS_Library\CSS_Library;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library\JS_Library;

abstract class Library_Pattern_Base implements Library_Pattern, JS_Library, CSS_Library {
	const NAME = '';

	/**
	 * @var array<string, bool>
	 */
	private array $js_handles;
	/**
	 * @var array<string, bool>
	 */
	private array $css_handles;
	private string $auto_discover_name;

	public function __construct() {
		$this->js_handles         = array();
		$this->css_handles        = array();
		$this->auto_discover_name = '';
	}

	protected function print_code_piece( string $name, string $piece_safe ): void {
		Code_Piece_Printer::print_code_piece( $this->auto_discover_name, $name, $piece_safe );
	}

	protected function print_js_code_piece(
		string $name,
		string $piece_safe,
		string $field_selector,
		bool $is_multiple
	): void {
		Code_Piece_Printer::print_js_code_piece(
			$this->auto_discover_name,
			$name,
			$piece_safe,
			$field_selector,
			$is_multiple
		);
	}

	/**
	 * @param array<string, bool> $js_handles
	 */
	protected function set_js_handles( array $js_handles ): void {
		$this->js_handles = $js_handles;
	}

	protected function enable_js_handle( string $js_handle ): void {
		$this->js_handles[ $js_handle ] = true;
	}

	protected function enable_css_handle( string $css_handle ): void {
		$this->css_handles[ $css_handle ] = true;
	}

	protected function is_enabled_js_handle( string $js_handle ): bool {
		return key_exists( $js_handle, $this->js_handles ) &&
				$this->js_handles[ $js_handle ];
	}

	/**
	 * @param array<string, bool> $css_handles
	 */
	protected function set_css_handles( array $css_handles ): void {
		$this->css_handles = $css_handles;
	}

	protected function set_auto_discover_name( string $auto_discover_name ): void {
		$this->auto_discover_name = $auto_discover_name;
	}

	public function get_js_origin(): string {
		return Asset_Resolver::ORIGIN_LITE;
	}

	public function has_active_js(): bool {
		$js_handles = $this->get_active_js_handles();

		return array() !== $js_handles;
	}

	public function get_active_js_handles(): array {
		return array_keys( array_filter( $this->js_handles ) );
	}

	public function get_external_scripts(): array {
		return array();
	}

	public function get_js_data(): array {
		return array();
	}

	public function has_active_css(): bool {
		$css_handles = $this->get_css_handles();
		$inline_css  = $this->get_inline_css();

		return array() !== $css_handles ||
				strlen( $inline_css ) > 0;
	}

	public function get_css_origin(): string {
		return $this->get_js_origin();
	}

	public function get_css_handles(): array {
		return array_keys( array_filter( $this->css_handles ) );
	}

	public function get_css_patches( Asset_Resolver $resolver ): array {
		return array();
	}

	public function get_inline_css(): string {
		return '';
	}

	public function get_auto_discover_name(): string {
		return $this->auto_discover_name;
	}

	public function get_name(): string {
		return static::NAME;
	}
}
