<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Layout_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;

class Layout_Integrations_Bootstrap extends Module_Bootstrap_Base {
	public function get_hookable_classes(): array {
		return array( Shortcode_Gutenberg_Block::class );
	}

	public function get_hookable_factories(): array {
		$factory = $this->resolve( Layout_Integrations_Factory::class );

		return array(
			Cpt_Item_Picker::class        => fn() => $factory->item_picker(),
			Layout_Gutenberg_Block::class => fn() => $factory->gutenberg_block(),
		);
	}

	public function resolve_extension_hookables(): array {
		if ( did_action( 'elementor/loaded' ) > 0 ) {
			$factory = $this->resolve( Layout_Integrations_Factory::class );

			return array(
				Cpt_Widget_Registrar::class    => fn() => $factory->elementor_widget_registrar(),
				Layout_Elementor_Assets::class => fn() => $factory->elementor_assets(),
			);
		}

		return array();
	}
}
