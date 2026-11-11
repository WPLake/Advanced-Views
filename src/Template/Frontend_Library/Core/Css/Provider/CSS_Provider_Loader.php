<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Provider;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Builder\CSS_Builder;

defined( 'ABSPATH' ) || exit;

final class CSS_Provider_Loader {
	private Asset_Resolver $asset_resolver;
	private CSS_Builder $css_builder;
	private string $css_code;

	public function __construct( Asset_Resolver $asset_resolver, CSS_Builder $css_builder ) {
		$this->asset_resolver = $asset_resolver;
		$this->css_builder    = $css_builder;
		$this->css_code       = '';
	}

	public function load_pattern( string $name, CSS_Provider $pattern ): void {
		$files_css  = $this->resolve_pattern_css( $pattern );
		$inline_css = $this->css_builder->build( $pattern->get_inline_styles() );

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

	protected function resolve_pattern_css( CSS_Provider $pattern ): string {
		$files_css = $this->read_pattern_files( $pattern );

		$patches = $pattern->get_style_patches( $this->asset_resolver );

		$subjects = array_keys( $patches );
		$replaces = array_values( $patches );

		return str_replace(
			$subjects,
			$replaces,
			$files_css
		);
	}

	protected function read_pattern_files( CSS_Provider $pattern ): string {
		$wp_filesystem = WP_Filesystem_Factory::get_wp_filesystem();
		$origin        = $pattern->get_style_origin();
		$css_handles   = $pattern->get_style_handles();
		$css           = '';

		foreach ( $css_handles as $css_handle ) {
			$style_file   = sprintf( 'css/front/%s.min.css', $css_handle );
			$path_to_file = $this->asset_resolver->get_origin_path( $origin, $style_file );

			$css .= (string) $wp_filesystem->get_contents( $path_to_file );
		}

		return $css;
	}
}
