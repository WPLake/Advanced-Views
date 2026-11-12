<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Admin_Assets;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Globals_Reader;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Rule_Merger;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Selection_Interactive_Fields;

class Assets_Factory extends Factory_Base {
	public function admin_assets(): Admin_Assets {
		return new Admin_Assets(
			$this->resolve( Asset_Resolver::class ),
			$this->interactive_fields()
		);
	}

	public function tailwind_rule_merger(): Tailwind_Rule_Merger {
		return new Tailwind_Rule_Merger(
			$this->resolve( Live_Reloader_Component::class )
		);
	}

	public function tailwind_globals_reader(): Tailwind_Globals_Reader {
		return new Tailwind_Globals_Reader(
			$this->resolve( Layout_Settings_Storage::class )->get_file_system()
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
