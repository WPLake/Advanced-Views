<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Resolver\Plugin_Assets;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Plugin\Plugin;

class Plugin_Module extends Module_Base {
	public static function get_type_definitions(): array {
		// Plugin instance itself is wired by the loader (Lite or Pro edition).
		return array(
			Plugin_Assets::class => Plugin::class,
		);
	}
}
