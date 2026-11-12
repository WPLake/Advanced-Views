<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style;

use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Globals_Reader;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Integrator;
use Org\Wplake\Advanced_Views\Plugin\Utils\Code_Minifier;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Asset_Collector;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Library_Style_Collector;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

final class Style_Tag_Composer {
	private Cpt_Asset_Collector $assets_collector;
	private Tailwind_Integrator $tailwind_integrator;
	private Tailwind_Globals_Reader $globals_reader;
	private Library_Style_Collector $style_collector;

	public function __construct(
		Cpt_Asset_Collector $assets_collector,
		Tailwind_Integrator $tailwind_integrator,
		Tailwind_Globals_Reader $globals_reader,
		Library_Style_Collector $style_collector
	) {
		$this->assets_collector    = $assets_collector;
		$this->tailwind_integrator = $tailwind_integrator;
		$this->globals_reader      = $globals_reader;
		$this->style_collector     = $style_collector;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function compose_tags(): array {
		$tags            = array();
		$assets_css_code = $this->style_collector->get_css_code();
		$is_assets_css   = strlen( $assets_css_code ) > 0;

		if ( $is_assets_css ) {
			$tags[] = self::compose_code_tag( array( 'data-advanced-views-assets' => true ), $assets_css_code );
		}

		$is_tailwind_in_use = false;
		$asset_css_codes    = $this->assets_collector->get_css_code();

		foreach ( $asset_css_codes as $name => $css_code ) {
			// the marker is a comment, so the check must precede the minification.
			$is_tailwind_asset = $this->tailwind_integrator->is_tailwind( $css_code );
			$asset_tag         = $this->compose_asset_tag( $name, $css_code, $is_tailwind_asset );

			$is_tailwind_in_use = $is_tailwind_in_use || $is_tailwind_asset;

			if ( $asset_tag instanceof Html_Tag ) {
				$tags[] = $asset_tag;
			}
		}

		$global_tailwind_styles = $is_tailwind_in_use ?
			$this->get_global_tailwind_styles() :
			'';
		$is_global_filled       = strlen( $global_tailwind_styles ) > 0;

		if ( $is_global_filled ) {
			$tags[] = self::compose_code_tag( array( 'data-advanced-views-tailwind-global' => true ), $global_tailwind_styles );
		}

		return $tags;
	}

	/**
	 * Drops the page-wide Tailwind state, which is no longer needed once the styles are composed.
	 */
	public function reset(): void {
		$this->tailwind_integrator->reset();
	}

	protected function compose_asset_tag( string $name, string $css_code, bool $is_tailwind_asset ): ?Html_Tag {
		$css_code = Code_Minifier::minify_css( $css_code );
		$css_code = $is_tailwind_asset ?
			$this->tailwind_integrator->merge( $css_code ) :
			$css_code;

		// can be empty after the minification or merging.
		$is_filled = strlen( $css_code ) > 0;

		if ( $is_filled ) {
			return self::compose_code_tag( array( 'data-advanced-views-asset' => $name ), $css_code );
		}

		return null;
	}

	protected function get_global_tailwind_styles(): string {
		$global_styles = $this->globals_reader->get_global_styles();

		return Code_Minifier::minify_css( $global_styles );
	}

	/**
	 * @param array<string,string|bool> $attrs
	 */
	protected static function compose_code_tag( array $attrs, string $code ): Html_Tag {
		return tag( 'style' )
			->attributes( $attrs )
			->content(
				raw_html( $code )
			);
	}
}
