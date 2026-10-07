<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;

class Layout_Integrations_Bootstrap extends Module_Bootstrap_Base {
	public function resolve_extension_hookables(): array {
		if ( did_action( 'elementor/loaded' ) > 0 ) {
			return $this->resolve( Layout_Integrations_Factory::class )
						->elementor_hookables();
		}

		return array();
	}

	public function get_hookable_classes(): array {
		return array( Shortcode_Gutenberg_Block::class );
	}

	protected function resolve_hookable_instances( Route_Detector $route_detector ): array {
		$factory         = $this->resolve( Layout_Integrations_Factory::class );
		$item_picker     = $factory->item_picker();
		$gutenberg_block = $factory->gutenberg_block();

		return array( $item_picker, $gutenberg_block );
	}
}
