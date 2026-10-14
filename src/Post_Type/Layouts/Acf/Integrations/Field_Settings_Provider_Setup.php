<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Integrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;

class Field_Settings_Provider_Setup implements Actor {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return true;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// groups are created by the container, so the only way to pass the cluster is a static setter.
		Field_Settings::set_provider_cluster( $this->provider_cluster );
	}
}
