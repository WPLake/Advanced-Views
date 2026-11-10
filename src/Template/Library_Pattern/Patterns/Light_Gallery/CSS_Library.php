<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Light_Gallery;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\CSS_Library\CSS_Library_Base;

defined( 'ABSPATH' ) || exit;

final class CSS_Library extends CSS_Library_Base {
	protected const HANDLE = 'lightgallery';
	protected const THUMBNAIL_HANDLE = 'lg-thumbnail';

	public function __construct() {
		parent::__construct(
			array(
				self::HANDLE,
				self::THUMBNAIL_HANDLE,
			)
		);
	}

	public function enable(): void {
		$this->enable_handle( self::HANDLE );

		// this addon is always in use.
		$this->enable_handle( self::THUMBNAIL_HANDLE );
	}

	public function get_css_patches( Asset_Resolver $resolver ): array {
		// font and image paths in CSS won't work, as CSS will be added right to the page,
		// replacing with the related installation path avoids it.
		$asset_url_base          = $resolver->get_origin_url( $this->get_css_origin(), '' );
		$relative_asset_url_base = Asset_Resolver::make_url_relative( $asset_url_base );

		return array(
			'url(../' => sprintf( 'url(%s', $relative_asset_url_base ),
		);
	}
}
