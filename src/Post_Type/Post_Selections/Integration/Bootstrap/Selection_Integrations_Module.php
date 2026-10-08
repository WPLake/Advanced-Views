<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Module_Base;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Elementor\Selection_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Gutenberg\Selection_Gutenberg_Block;

class Selection_Integrations_Module extends Module_Base {
	public static function get_hookable_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Selection_Integrations_Factory::class );

		return array(
			Cpt_Item_Picker::class           => fn() => $factory->item_picker(),
			Selection_Gutenberg_Block::class => fn() => $factory->gutenberg_block(),
		);
	}

	public static function resolve_extension_hookables( Instance_Container $container ): array {
		if ( did_action( 'elementor/loaded' ) > 0 ) {
			$factory = $container->resolve( Selection_Integrations_Factory::class );

			return array(
				Cpt_Widget_Registrar::class       => fn() => $factory->elementor_widget_registrar(),
				Selection_Elementor_Assets::class => fn() => $factory->elementor_assets(),
			);
		}

		return array();
	}
}
