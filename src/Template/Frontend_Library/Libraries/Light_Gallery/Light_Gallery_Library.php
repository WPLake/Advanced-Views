<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;

defined( 'ABSPATH' ) || exit;

final class Light_Gallery_Library implements Frontend_Library {
	public static function get_name(): string {
		return 'light-gallery';
	}

	public static function get_docs_url(): string {
		return 'https://www.lightgalleryjs.com/docs/settings/#lightgallery-core';
	}

	public static function get_css_file_names(): array {
		return array(
			'lightgallery',
			'lg-thumbnail',
		);
	}

	public static function get_css_patches( Asset_Resolver $resolver ): array {
		$base_url     = $resolver->get_asset_url( '' );
		$relative_url = Asset_Resolver::make_url_relative( $base_url );

		$patched_url = sprintf( 'url(%s', $relative_url );

		return array(
			'url(../' => $patched_url,
		);
	}

	public static function get_js_file_names(): array {
		return array(
			'lightgallery',
			'lg-thumbnail',
		);
	}
}
