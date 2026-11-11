<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Asset_Providers;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider_Base;

defined( 'ABSPATH' ) || exit;

final class Style_Provider extends Style_Provider_Base {
	public function enable(): void {
		$this->enable_handles(
			array(
				'lightgallery',
				// this addon is always in use.
				'lg-thumbnail',
			)
		);
	}

	public function get_style_patches( Asset_Resolver $resolver ): array {
		// font and image paths in CSS won't work, as CSS will be added right to the page,
		// replacing with the related installation path avoids it.
		$asset_url_base          = $resolver->get_origin_url( $this->get_style_origin(), '' );
		$relative_asset_url_base = Asset_Resolver::make_url_relative( $asset_url_base );

		return array(
			'url(../' => sprintf( 'url(%s', $relative_asset_url_base ),
		);
	}
}
