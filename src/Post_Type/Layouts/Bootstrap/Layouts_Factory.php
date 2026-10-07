<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layouts_Cpt as Layouts_Cpt_Hookable;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Fields\Field_Markup;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Markup;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;

class Layouts_Factory extends Container_Facade {
	public function create_layout_factory(): Layout_Factory {
		$front_assets     = $this->resolve( Front_Assets::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );
		$markup           = $this->resolve( Layout_Markup::class );
		$renderer_storage = $this->resolve( Template_Renderer_Storage::class );
		$field_markup     = $this->resolve( Field_Markup::class );
		$provider_cluster = $this->resolve( Field_Provider_Cluster::class );

		return new Layout_Factory(
			$front_assets,
			PHP_Template_Engine::NAME,
			$settings_storage,
			$markup,
			$renderer_storage,
			$field_markup,
			$provider_cluster
		);
	}

	public function create_cpt_hookable(): Layouts_Cpt_Hookable {
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );
		$settings_storage = $this->resolve( Layout_Settings_Storage::class );

		return new Layouts_Cpt_Hookable( $layouts_cpt, $settings_storage );
	}

	public function create_shortcode_block(): Shortcode_Gutenberg_Block {
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$shortcodes  = $layouts_cpt->shortcodes();

		return new Shortcode_Gutenberg_Block( $shortcodes );
	}

	public function create_editor_settings(): Cpt_Gutenberg_Editor_Settings {
		$layouts_cpt = $this->resolve( Layouts_Cpt::class );
		$cpt_name    = $layouts_cpt->cpt_name();

		return new Cpt_Gutenberg_Editor_Settings( $cpt_name );
	}

	public function create_assets_reducer(): Cpt_Assets_Reducer {
		$settings_storage = $this->resolve( Settings_Storage::class );
		$plugin           = $this->resolve( Plugin::class );
		$layouts_cpt      = $this->resolve( Layouts_Cpt::class );
		$cpt_name         = $layouts_cpt->cpt_name();

		return new Cpt_Assets_Reducer( $settings_storage, $plugin, $cpt_name );
	}
}
