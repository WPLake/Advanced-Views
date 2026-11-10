<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;

abstract class Library_Pattern_Base implements Library_Pattern {
	const NAME = '';

	/**
	 * @var array<string, bool>
	 */
	private array $js_handles;
	/**
	 * @var array<string, bool>
	 */
	private array $css_handles;
	private Asset_Resolver $asset_resolver;
	private string $auto_discover_name;

	public function __construct( Asset_Resolver $asset_resolver ) {
		$this->asset_resolver     = $asset_resolver;
		$this->js_handles         = array();
		$this->css_handles        = array();
		$this->auto_discover_name = '';
	}

	protected function print_code_piece( string $name, string $piece_safe ): void {
		Code_Piece_Printer::print_code_piece( $this->auto_discover_name, $name, $piece_safe );
	}

	protected function get_asset_url( string $file ): string {
		return $this->asset_resolver->get_asset_url( $file );
	}

	protected function get_asset_path( string $file ): string {
		return $this->asset_resolver->get_asset_path( $file );
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

	public function get_asset_origin(): string {
		return self::ORIGIN_LITE;
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

	public function get_active_css(): string {
		$css = '';

		$wp_filesystem = WP_Filesystem_Factory::get_wp_filesystem();

		foreach ( $this->css_handles as $css_handle => $is_active ) {
			if ( $is_active ) {
				$style_file   = sprintf( 'css/front/%s.min.css', $css_handle );
				$path_to_file = $this->get_asset_path( $style_file );

				$css .= (string) $wp_filesystem->get_contents( $path_to_file );
			}
		}

		return $css;
	}

	public function get_auto_discover_name(): string {
		return $this->auto_discover_name;
	}

	public function get_name(): string {
		return static::NAME;
	}
}
