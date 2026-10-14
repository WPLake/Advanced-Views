<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin;

defined( 'ABSPATH' ) || exit;

final class Plugin_Meta {
	private string $main_file;
	private string $version;
	private string $root_url;
	private string $root_path;
	// acf-views/acf-views.php.
	private string $slug;
	// acf-views.
	private string $short_slug;

	public function __construct( string $main_file ) {
		$this->main_file = $main_file;
		$this->version   = self::detect_plugin_version( $main_file );

		$this->root_url  = plugin_dir_url( $main_file );
		$this->root_path = plugin_dir_path( $main_file );

		$this->slug       = plugin_basename( $main_file );
		$this->short_slug = dirname( $this->slug );
	}

	public function get_main_file(): string {
		return $this->main_file;
	}

	public function get_version(): string {
		return $this->version;
	}

	public function get_root_url(): string {
		return $this->root_url;
	}

	public function get_root_path( string $inner_path = '' ): string {
		return $this->root_path . $inner_path;
	}

	public function get_slug(): string {
		return $this->slug;
	}

	public function get_short_slug(): string {
		return $this->short_slug;
	}

	public function get_relative_path( string $inner_path ): string {
		return $this->short_slug . '/' . $inner_path;
	}

	protected static function detect_plugin_version( string $plugin_file ): string {
		// @phpcs:ignore
		$plugin_file_content = (string)file_get_contents($plugin_file);

		preg_match( '/Version:(.*)/', $plugin_file_content, $matches );

		$current_version_number = $matches[1] ?? '1.0.0';

		return trim( $current_version_number );
	}
}
