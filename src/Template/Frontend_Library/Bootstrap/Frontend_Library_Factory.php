<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Lightbox_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Map\Map_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Light_Gallery_Library;

class Frontend_Library_Factory extends Factory_Base {
	public function library_container(): Library_Container {
		return new Library_Container(
			$this->resolve( Instance_Container::class ),
			$this->library_classes()
		);
	}

	/**
	 * @return class-string<Frontend_Library>[]
	 */
	protected function library_classes(): array {
		return array(
			Map_Library::class,
			Light_Gallery_Library::class,
			Lightbox_Library::class,
		);
	}
}
