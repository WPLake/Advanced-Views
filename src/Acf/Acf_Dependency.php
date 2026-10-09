<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Resolver\Asset_Resolver;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;

class Acf_Dependency extends Actor_Base implements Actor {
	private Asset_Resolver $asset_resolver;

	public function __construct( Asset_Resolver $asset_resolver ) {
		$this->asset_resolver = $asset_resolver;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route() &&
			( $route_detector->is_cpt_admin_route( Hard_Layout_Cpt::cpt_name() ) ||
				$route_detector->is_cpt_admin_route( Hard_Post_Selection_Cpt::cpt_name() ) ||
				wp_doing_ajax() );
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action(
			'plugins_loaded',
			array( $this, 'maybe_include_acf_plugin' ),
			// -2, so it's before Acf_Internal_Features
			Field_Provider_Cluster::PLUGINS_LOADED_HOOK_PRIORITY - 2
		);
	}

	public static function is_acf_plugin_available( bool $is_pro_only = false ): bool {
		// don't use 'is_plugin_active()' as the function available lately.
		return class_exists( 'acf_pro' ) ||
			( ! $is_pro_only && class_exists( 'ACF' ) );
	}

	public function maybe_include_acf_plugin(): void {
		if ( self::is_acf_plugin_available() ) {
			return;
		}

		$acf_file       = $this->asset_resolver->get_standalone_vendor_path( 'advanced-custom-fields/acf.php' );
		$acf_plugin_url = $this->asset_resolver->get_standalone_vendor_url( 'advanced-custom-fields/' );

		// Hide ACF admin menu (as we loaded ACF only for our plugin).
		self::add_filter( 'acf/settings/show_admin', '__return_false' );
		// ensure right url, otherwise internal ACF asset paths are incorrect.
		self::add_filter( 'acf/settings/url', fn() => $acf_plugin_url );

		require_once $acf_file;

		// used in the AcfDataVendor to skip loading if it's inner ACF.
		define( 'ACF_VIEWS_INNER_ACF', true );
	}
}
