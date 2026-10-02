<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

defined( 'ABSPATH' ) || exit;

class Asset_Resolver {
	private string $plugin_url;
	private string $plugin_path;
	private string $plugin_version;

	public function __construct( string $main_plugin_file, string $plugin_version ) {
		$this->plugin_url     = plugin_dir_url( $main_plugin_file );
		$this->plugin_path    = plugin_dir_path( $main_plugin_file );
		$this->plugin_version = $plugin_version;
	}

	public function get_asset_url( string $file ): string {
		return $this->plugin_url . 'src/Assets/' . $file;
	}

	public function get_asset_path( string $file ): string {
		return $this->plugin_path . 'src/Assets/' . $file;
	}

	public function get_version(): string {
		return $this->plugin_version;
	}

	public function get_standalone_vendor_path( string $sub_path ): string {
		return $this->plugin_path . 'vendor/standalone/' . $sub_path;
	}

	public function get_standalone_vendor_url( string $sub_path ): string {
		return $this->plugin_url . 'vendor/standalone/' . $sub_path;
	}

	protected function get_plugin_url(): string {
		return $this->plugin_url;
	}

	protected function get_plugin_path(): string {
		return $this->plugin_path;
	}
}
