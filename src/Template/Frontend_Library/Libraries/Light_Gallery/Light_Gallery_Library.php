<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;

defined( 'ABSPATH' ) || exit;

final class Light_Gallery_Library implements Frontend_Library {
	public static function get_docs_url(): string {
		return 'https://www.lightgalleryjs.com/docs/settings/#lightgallery-core';
	}
}
