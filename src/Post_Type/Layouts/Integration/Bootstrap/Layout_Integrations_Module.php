<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Layout_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;

class Layout_Integrations_Module extends Module_Base {
	public static function get_actor_classes(): array {
		return array( Shortcode_Gutenberg_Block::class );
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Layout_Integrations_Factory::class );

		return array(
			Cpt_Item_Picker::class        => fn() => $factory->item_picker(),
			Layout_Gutenberg_Block::class => fn() => $factory->gutenberg_block(),
		);
	}

	public static function resolve_extension_actors( Instance_Container $container ): array {
		if ( did_action( 'elementor/loaded' ) > 0 ) {
			$factory = $container->resolve( Layout_Integrations_Factory::class );

			return array(
				Cpt_Widget_Registrar::class    => fn() => $factory->elementor_widget_registrar(),
				Layout_Elementor_Assets::class => fn() => $factory->elementor_assets(),
			);
		}

		return array();
	}
}
