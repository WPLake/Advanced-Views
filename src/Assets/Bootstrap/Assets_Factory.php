<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Admin_Assets;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Code_Marker;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;

class Assets_Factory extends Factory_Base {
	public function front_assets(): Front_Assets {
		return new Front_Assets(
			$this->resolve( Asset_Resolver::class ),
			$this->resolve( Layout_Settings_Storage::class )->get_file_system(),
			$this->resolve( Live_Reloader_Component::class ),
			$this->resolve( Html_Renderer::class ),
			$this->resolve( Code_Marker::class ),
			$this->resolve( Style_Builder::class ),
			$this->resolve( Library_Container::class )
		);
	}

	public function admin_assets(): Admin_Assets {
		return new Admin_Assets(
			$this->resolve( Asset_Resolver::class ),
			$this->interactive_fields()
		);
	}

	/**
	 * @return Cpt_Interactive_Fields[]
	 */
	protected function interactive_fields(): array {
		return array(
			$this->resolve( Layout_Interactive_Fields::class ),
			$this->resolve( Selection_Interactive_Fields::class ),
		);
	}
}
