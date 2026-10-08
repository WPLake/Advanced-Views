<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Facade\Factory_Facade;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Gutenberg\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Layout_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

class Layout_Integrations_Factory extends Factory_Facade {
	protected ?Cpt_Item_Picker $item_picker = null;
	protected ?Cpt_Renderer $renderer       = null;

	public function item_picker(): Cpt_Item_Picker {
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );

		$this->item_picker ??= new Cpt_Item_Picker( $settings_storage, $layouts_cpt );

		return $this->item_picker;
	}

	public function renderer(): Cpt_Renderer {
		$layout_shortcode = $this->resolve( Layout_Shortcode::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );

		$this->renderer ??= new Cpt_Renderer( $layout_shortcode, $settings_storage, $layouts_cpt );

		return $this->renderer;
	}

	public function gutenberg_block(): Layout_Gutenberg_Block {
		$asset_resolver = $this->resolve( Asset_Resolver::class );
		$item_picker    = $this->item_picker();
		$renderer       = $this->renderer();

		$cpt_block = new Cpt_Gutenberg_Block( $renderer );

		return new Layout_Gutenberg_Block( $asset_resolver, $item_picker, $cpt_block );
	}

	public function elementor_widget_registrar(): Cpt_Widget_Registrar {
		$item_picker = $this->item_picker();
		$renderer    = $this->renderer();

		$widget_registrar = new Cpt_Widget_Registrar( $item_picker, $renderer );
		$widget_registrar->add_widget( Layout_Elementor_Widget::class );

		return $widget_registrar;
	}

	public function elementor_assets(): Layout_Elementor_Assets {
		$item_picker    = $this->item_picker();
		$asset_resolver = $this->resolve( Asset_Resolver::class );

		return new Layout_Elementor_Assets( $item_picker, $asset_resolver );
	}
}
