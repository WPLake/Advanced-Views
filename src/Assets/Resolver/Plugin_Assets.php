<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Resolver;

defined( 'ABSPATH' ) || exit;

interface Plugin_Assets {
	public function get_root_url(): string;

	public function get_root_path(): string;

	public function get_assets_version(): string;
}
