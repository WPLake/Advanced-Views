<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Bridge\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;

class Bridge_Bootstrap extends Bootstrap_Base {
	public static function get_actor_classes(): array {
		return array( Shortcode_Renderers::class );
	}
}
