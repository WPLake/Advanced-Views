<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Container_Facade;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Api_Interface;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Query;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Selection_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Selection_Markup;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\PHP\PHP_Template_Engine;

class Post_Selections_Factory extends Container_Facade {
	public function create_selection_factory(): Post_Selection_Factory {
		$front_assets     = $this->resolve( Front_Assets::class );
		$post_query       = $this->resolve( Post_Query::class );
		$markup           = $this->resolve( Post_Selection_Markup::class );
		$renderer_storage = $this->resolve( Template_Renderer_Storage::class );
		$settings_storage = $this->resolve( Selection_Settings_Storage::class );

		return new Post_Selection_Factory(
			$front_assets,
			PHP_Template_Engine::NAME,
			$post_query,
			$markup,
			$renderer_storage,
			$settings_storage
		);
	}

	public function create_git_box(): Selection_Git_Box {
		$selections_cpt   = $this->resolve( Selections_Cpt::class );
		$settings         = $this->resolve( Settings_Storage::class );
		$settings_storage = $this->resolve( Selection_Settings_Storage::class );
		$git_api          = $this->resolve( Git_Api_Interface::class );
		$layouts_storage  = $this->resolve( Layout_Settings_Storage::class );
		$layouts_git_box  = $this->resolve( Layout_Git_Box::class );
		$plugin           = $this->resolve( Plugin::class );

		return new Selection_Git_Box(
			$selections_cpt,
			$settings,
			$settings_storage,
			$git_api,
			$layouts_storage,
			$layouts_git_box,
			$plugin
		);
	}

	public function create_editor_settings(): Cpt_Gutenberg_Editor_Settings {
		$selections_cpt = $this->resolve( Selections_Cpt::class );
		$cpt_name       = $selections_cpt->cpt_name();

		return new Cpt_Gutenberg_Editor_Settings( $cpt_name );
	}

	public function create_assets_reducer(): Cpt_Assets_Reducer {
		$settings_storage = $this->resolve( Settings_Storage::class );
		$plugin           = $this->resolve( Plugin::class );
		$selections_cpt   = $this->resolve( Selections_Cpt::class );
		$cpt_name         = $selections_cpt->cpt_name();

		return new Cpt_Assets_Reducer( $settings_storage, $plugin, $cpt_name );
	}
}
