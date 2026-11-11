<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\JavaScript\Library\JS_Library_Base;

defined( 'ABSPATH' ) || exit;

final class JS_Library extends JS_Library_Base {
	public function enable(): void {
		$this->enable_handles(
			array(
				'lightgallery',
				// this addon is always in use.
				'lg-thumbnail',
			)
		);
	}
}
