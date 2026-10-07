<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Core\Bootstrap\Module_Bootstrap_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Elementor\Cpt_Widget_Registrar;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Gutenberg\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Assets;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor\Layout_Elementor_Widget;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Layout_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

class Layout_Integrations_Bootstrap extends Module_Bootstrap_Base {
	protected ?Cpt_Item_Picker $item_picker = null;
	protected ?Cpt_Renderer $renderer       = null;

	public function get_hookables( Route_Detector $route_detector ): array {
		$item_picker     = $this->item_picker();
		$gutenberg_block = $this->create_gutenberg_block();

		return array(
			$item_picker,
			$gutenberg_block,
			$this->create_shortcode_block(),
		);
	}

	public function get_extension_hookables(): array {
		if ( did_action( 'elementor/loaded' ) > 0 ) {
			return $this->create_elementor_hookables();
		}

		return array();
	}

	/**
	 * @return Hookable[]
	 */
	protected function create_elementor_hookables(): array {
		$item_picker    = $this->item_picker();
		$renderer       = $this->renderer();
		$asset_resolver = $this->resolve( Asset_Resolver::class );

		$widget_registrar = new Cpt_Widget_Registrar( $item_picker, $renderer );

		$widget_registrar->add_widget( Layout_Elementor_Widget::class );

		return array(
			$widget_registrar,
			new Layout_Elementor_Assets( $item_picker, $asset_resolver ),
		);
	}

	protected function create_gutenberg_block(): Layout_Gutenberg_Block {
		$asset_resolver = $this->resolve( Asset_Resolver::class );
		$item_picker    = $this->item_picker();
		$renderer       = $this->renderer();

		$cpt_block = new Cpt_Gutenberg_Block( $renderer );

		return new Layout_Gutenberg_Block( $asset_resolver, $item_picker, $cpt_block );
	}

	protected function create_shortcode_block(): Shortcode_Gutenberg_Block {
		return $this->resolve( Shortcode_Gutenberg_Block::class );
	}

	protected function item_picker(): Cpt_Item_Picker {
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );

		$this->item_picker ??= new Cpt_Item_Picker( $settings_storage, $layouts_cpt );

		return $this->item_picker;
	}

	protected function renderer(): Cpt_Renderer {
		$layout_shortcode = $this->resolve( Layout_Shortcode::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );

		$this->renderer ??= new Cpt_Renderer( $layout_shortcode, $settings_storage, $layouts_cpt );

		return $this->renderer;
	}
}
