<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Light_Gallery;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library\JS_Pattern_Base;

defined( 'ABSPATH' ) || exit;

final class Light_Gallery_JS extends JS_Pattern_Base {
	public function __construct() {
		parent::__construct(
			array(
				'lightgallery',
				'lg-thumbnail',
			)
		);
	}

	public function enable(): void {
		$this->enable_handle( 'lightgallery' );

		// this addon is always in use.
		$this->enable_handle( 'lg-thumbnail' );
	}
}
