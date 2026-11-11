<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Asset_Providers;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\JavaScript\Provider\Scripts_Provider_Base;

defined( 'ABSPATH' ) || exit;

final class Scripts_Provider extends Scripts_Provider_Base {
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
