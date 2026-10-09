<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf;

use Org\Wplake\Advanced_Views\Assets\Resolver\Asset_Resolver;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;

class Acf_Internal_Features extends Actor_Base implements Actor {
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
		// only since 'plugins_loaded' we can judge if ACF is loaded or not
		// '-1' so it's after AcfDependency->maybeIncludeAcfPlugin().
		self::add_action(
			'plugins_loaded',
			array( $this, 'maybe_include_features' ),
			Field_Provider_Cluster::PLUGINS_LOADED_HOOK_PRIORITY - 1
		);
	}

	public function include_field_types(): void {
		$internal_features_path = $this->asset_resolver->get_standalone_vendor_path( 'acf-internal-features' );

		include_once $internal_features_path . '/inc/class-acf-field-clone.php';
		include_once $internal_features_path . '/inc/class-acf-repeater-table.php';
		include_once $internal_features_path . '/inc/class-acf-field-repeater.php';
		include_once $internal_features_path . '/inc/options-page.php';
		include_once $internal_features_path . '/inc/admin-options-page.php';
		include_once $internal_features_path . '/inc/class-acf-location-options-page.php';
	}

	public function register_assets(): void {
		// register scripts.
		wp_register_script(
			'acf-pro-input',
			$this->asset_resolver->get_standalone_vendor_url( 'acf-internal-features/assets/acf-pro-input.min.js' ),
			array( 'acf-input' ),
			$this->asset_resolver->get_version(),
			array(
				'in_footer' => false,
			)
		);

		// register styles.
		wp_register_style(
			'acf-pro-input',
			$this->asset_resolver->get_standalone_vendor_url( 'acf-internal-features/assets/acf-pro-input.min.css' ),
			array( 'acf-input' ),
			$this->asset_resolver->get_version()
		);
	}

	public function input_admin_enqueue_scripts(): void {
		wp_enqueue_script( 'acf-pro-input' );
		wp_enqueue_style( 'acf-pro-input' );
	}

	public function maybe_include_features(): void {
		// skip if 'ACF Pro' is available.

		if ( Acf_Dependency::is_acf_plugin_available( true ) ) {
			return;
		}

		self::add_action( 'init', array( $this, 'register_assets' ) );
		self::add_action( 'acf/include_field_types', array( $this, 'include_field_types' ), 5 );
		self::add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'input_admin_enqueue_scripts' ) );
	}
}
