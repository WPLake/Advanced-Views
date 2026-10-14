<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Environment;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Meta;

final class Plugin_Translations implements Actor {
	private Plugin_Meta $plugin_meta;
	/**
	 * @var array<string, string> domain => relative_path
	 */
	private array $lang_relative_paths;

	/**
	 * @param array<string, string> $lang_relative_paths domain => relative_path
	 */
	public function __construct( Plugin_Meta $plugin_meta, array $lang_relative_paths ) {
		$this->plugin_meta         = $plugin_meta;
		$this->lang_relative_paths = $lang_relative_paths;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		// on the whole admin area, as menu items need translations.
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// priority 8: make sure it's before the acf groups loading.
		add_action( 'after_setup_theme', [$this,'load_textdomains'], 8 );
	}

	public function load_textdomains():void{
		foreach ( $this->lang_relative_paths as $domain => $relative_path ) {
			$path = $this->plugin_meta->get_relative_path( $relative_path );

			load_plugin_textdomain(
				$domain,
				false,
				$path
			);
		}
	}
}
