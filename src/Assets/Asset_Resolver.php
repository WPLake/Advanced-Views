<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

defined( 'ABSPATH' ) || exit;

interface Asset_Resolver {
	public function get_asset_url( string $file ): string;

	public function get_asset_path( string $file ): string;

	public function get_version(): string;
}
