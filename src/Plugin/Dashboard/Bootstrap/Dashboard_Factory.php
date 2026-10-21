<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Admin_Pages;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Import_Printer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Toolbar_Printer;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;

class Dashboard_Factory extends Factory_Base {
	public function admin_pages(): Admin_Pages {
		return new Admin_Pages(
			$this->resolve( Plugin::class ),
			$this->resolve( Plugin_Meta::class ),
			$this->resolve( Toolbar_Printer::class ),
			$this->resolve( Import_Printer::class ),
			$this->resolve( Demo_Importer::class ),
			$this->plugin_cpts()
		);
	}

	/**
	 * @return Plugin_Cpt[]
	 */
	protected function plugin_cpts(): array {
		return array(
			$this->resolve( Layouts_Cpt::class ),
			$this->resolve( Selections_Cpt::class ),
		);
	}
}
