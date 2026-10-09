<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Resolver;

defined( 'ABSPATH' ) || exit;

class Asset_Resolver {
	protected Plugin_Assets $plugin_assets;

	public function __construct( Plugin_Assets $plugin_assets ) {
		$this->plugin_assets = $plugin_assets;
	}

	public function get_asset_url( string $file ): string {
		$root_url = $this->plugin_assets->get_root_url();

		$parts = array(
			$root_url,
			'src/Assets/',
			$file,
		);

		return join( '', $parts );
	}

	public function get_asset_path( string $file ): string {
		$root_path = $this->plugin_assets->get_root_path();

		$parts = array(
			$root_path,
			'src/Assets/',
			$file,
		);

		return join( '', $parts );
	}

	public function get_version(): string {
		return $this->plugin_assets->get_assets_version();
	}

	public function get_standalone_vendor_path( string $sub_path ): string {
		$root_path = $this->plugin_assets->get_root_path();

		$parts = array(
			$root_path,
			'vendor/standalone/',
			$sub_path,
		);

		return join( '', $parts );
	}

	public function get_standalone_vendor_url( string $sub_path ): string {
		$root_url = $this->plugin_assets->get_root_url();

		$parts = array(
			$root_url,
			'vendor/standalone/',
			$sub_path,
		);

		return join( '', $parts );
	}
}
