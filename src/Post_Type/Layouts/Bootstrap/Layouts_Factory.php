<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

class Layouts_Factory extends Container_Facade {
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
