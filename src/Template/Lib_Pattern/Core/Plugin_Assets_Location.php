<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Plugin;

class Plugin_Assets_Location implements Assets_Location {
	protected Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function url( string $file ): string {
		return $this->plugin->get_assets_url( $file );
	}

	public function path( string $file ): string {
		return $this->plugin->get_assets_path( $file );
	}

	public function version(): string {
		return $this->plugin->get_version();
	}
}
