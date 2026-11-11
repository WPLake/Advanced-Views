<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;

defined( 'ABSPATH' ) || exit;

final class Style_Loader {
	private Asset_Resolver $asset_resolver;
	private Style_Builder $style_builder;
	private string $code;

	public function __construct( Asset_Resolver $asset_resolver, Style_Builder $style_builder ) {
		$this->asset_resolver = $asset_resolver;
		$this->style_builder  = $style_builder;
		$this->code           = '';
	}

	public function enqueue_styles( string $name, Style_Provider $provider ): void {
		$files_code  = $this->resolve_styles( $provider );
		$inline_code = $this->style_builder->build( $provider->get_inline_styles() );

		$code      = $files_code . $inline_code;
		$is_filled = strlen( $code ) > 0;

		if ( $is_filled ) {
			// no escaping, it's a CSS code, so e.g '.a > .b' shouldn't be escaped.
			$this->code .= sprintf(
				"/*%s*/\n%s\n",
				$name,
				$code
			);
		}
	}

	public function get_css_code(): string {
		return $this->code;
	}

	protected function resolve_styles( Style_Provider $provider ): string {
		$files_code = $this->read_files( $provider );

		$patches = $provider->get_style_patches( $this->asset_resolver );

		$subjects = array_keys( $patches );
		$replaces = array_values( $patches );

		return str_replace(
			$subjects,
			$replaces,
			$files_code
		);
	}

	protected function read_files( Style_Provider $provider ): string {
		$wp_filesystem = WP_Filesystem_Factory::get_wp_filesystem();
		$origin        = $provider->get_style_origin();
		$handles   = $provider->get_style_handles();
		$code           = '';

		foreach ( $handles as $handle ) {
			$style_file   = sprintf( 'css/front/%s.min.css', $handle );
			$path_to_file = $this->asset_resolver->get_origin_path( $origin, $style_file );

			$code .= (string) $wp_filesystem->get_contents( $path_to_file );
		}

		return $code;
	}
}
