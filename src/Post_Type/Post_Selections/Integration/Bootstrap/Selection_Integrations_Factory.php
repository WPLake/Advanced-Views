<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Gutenberg\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Elementor\Selection_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Elementor\Selection_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Gutenberg\Selection_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Post_Selection_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Selection_Integrations_Factory extends Container_Facade {
	protected ?Cpt_Item_Picker $item_picker = null;
	protected ?Cpt_Renderer $renderer       = null;

	public function item_picker(): Cpt_Item_Picker {
		$settings_storage = $this->resolve( Selection_Settings_Storage::class );
		$selections_cpt   = $this->resolve( Selections_Cpt::class );

		$this->item_picker ??= new Cpt_Item_Picker( $settings_storage, $selections_cpt );

		return $this->item_picker;
	}

	public function renderer(): Cpt_Renderer {
		$shortcode        = $this->resolve( Post_Selection_Shortcode::class );
		$settings_storage = $this->resolve( Selection_Settings_Storage::class );
		$selections_cpt   = $this->resolve( Selections_Cpt::class );

		$this->renderer ??= new Cpt_Renderer( $shortcode, $settings_storage, $selections_cpt );

		return $this->renderer;
	}

	public function gutenberg_block(): Selection_Gutenberg_Block {
		$asset_resolver = $this->resolve( Asset_Resolver::class );
		$item_picker    = $this->item_picker();
		$renderer       = $this->renderer();

		$cpt_block = new Cpt_Gutenberg_Block( $renderer );

		return new Selection_Gutenberg_Block( $asset_resolver, $item_picker, $cpt_block );
	}

	public function elementor_widget_registrar(): Cpt_Widget_Registrar {
		$item_picker = $this->item_picker();
		$renderer    = $this->renderer();

		$widget_registrar = new Cpt_Widget_Registrar( $item_picker, $renderer );
		$widget_registrar->add_widget( Selection_Elementor_Widget::class );

		return $widget_registrar;
	}

	public function elementor_assets(): Selection_Elementor_Assets {
		$item_picker    = $this->item_picker();
		$asset_resolver = $this->resolve( Asset_Resolver::class );

		return new Selection_Elementor_Assets( $item_picker, $asset_resolver );
	}
}
