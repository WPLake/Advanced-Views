<?php

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;

final class Version_Migrations_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookable( Route_Detector $route_detector ): Hookable {
		// TODO: Implement get_hookable() method.
	}
}
