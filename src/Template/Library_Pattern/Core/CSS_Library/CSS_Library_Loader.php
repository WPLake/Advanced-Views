<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\CSS_Library;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;

defined( 'ABSPATH' ) || exit;

final class CSS_Library_Loader {
	private Asset_Resolver $asset_resolver;
	private string $css_code;

	public function __construct( Asset_Resolver $asset_resolver ) {
		$this->asset_resolver = $asset_resolver;
		$this->css_code       = '';
	}

	public function load_pattern( string $name, CSS_Library $pattern ): void {
		$files_css  = $this->get_files_css( $pattern );
		$inline_css = $pattern->get_inline_css();

		$css_code  = $files_css . $inline_css;
		$is_filled = strlen( $css_code ) > 0;

		if ( $is_filled ) {
			// no escaping, it's a CSS code, so e.g '.a > .b' shouldn't be escaped.
			$this->css_code .= sprintf(
				"/*%s*/\n%s\n",
				$name,
				$css_code
			);
		}
	}

	public function get_css_code(): string {
		return $this->css_code;
	}

	protected function get_files_css( CSS_Library $pattern ): string {
		$files_css = $this->read_files( $pattern );
		$patches   = $pattern->get_css_patches( $this->asset_resolver );
		$subjects  = array_keys( $patches );
		$replaces  = array_values( $patches );

		return str_replace(
			$subjects,
			$replaces,
			$files_css
		);
	}

	protected function read_files( CSS_Library $pattern ): string {
		$wp_filesystem = WP_Filesystem_Factory::get_wp_filesystem();
		$origin        = $pattern->get_css_origin();
		$css_handles   = $pattern->get_css_handles();
		$css           = '';

		foreach ( $css_handles as $css_handle ) {
			$style_file   = sprintf( 'css/front/%s.min.css', $css_handle );
			$path_to_file = $this->asset_resolver->get_origin_path( $origin, $style_file );

			$css .= (string) $wp_filesystem->get_contents( $path_to_file );
		}

		return $css;
	}
}
