<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\CSS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\File_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\JS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns\Inline_Gallery;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns\Lightbox_Multi;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns\Lightbox_Single;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Gallery_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;

final class Light_Gallery_Library implements Frontend_Library, CSS_Library, JS_Library {
	public static function get_id(): string {
		return 'light-gallery';
	}

	public static function get_label(): string {
		return __( 'LightGallery', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return 'https://www.lightgalleryjs.com/docs/settings/#lightgallery-core';
	}

	public static function get_patterns(): array {
		return array(
			Image_Field::class   => array(
				Lightbox_Single::class,
			),
			Gallery_Field::class => array(
				Inline_Gallery::class,
				Lightbox_Multi::class,
			),
		);
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

	public static function get_js_files( File_Context $context ): array {
		return array(
			'lightgallery',
			'lg-thumbnail',
		);
	}
}
