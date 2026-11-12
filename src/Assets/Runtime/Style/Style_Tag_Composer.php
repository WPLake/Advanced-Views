<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style;

use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Deduplicator;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Globals;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Parser;
use Org\Wplake\Advanced_Views\Plugin\Utils\Code_Minifier;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Asset_Collector;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Library_Style_Collector;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

final class Style_Tag_Composer {
	private Cpt_Asset_Collector $assets_collector;
	private Library_Style_Collector $style_collector;
	private Tailwind_Deduplicator $tailwind_deduplicator;
	private Tailwind_Globals $tailwind_globals;

	public function __construct(
		Cpt_Asset_Collector $assets_collector,
		Library_Style_Collector $style_collector,
		Tailwind_Deduplicator $rule_filter,
		Tailwind_Globals $tailwind_globals
	) {
		$this->assets_collector      = $assets_collector;
		$this->style_collector       = $style_collector;
		$this->tailwind_deduplicator = $rule_filter;
		$this->tailwind_globals      = $tailwind_globals;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function compose_tags(): array {
		$library_tags = $this->compose_library_tags();
		// the order matters: the Tailwind globals are available only after the assets are filtered.
		$asset_tags  = $this->compose_asset_tags();
		$global_tags = $this->compose_global_tags();

		return array_merge(
			$library_tags,
			$asset_tags,
			$global_tags
		);
	}

	/**
	 * @return Html_Tag[]
	 */
	protected function compose_library_tags(): array {
		$css_code  = $this->style_collector->get_css_code();
		$is_filled = strlen( $css_code ) > 0;

		if ( $is_filled ) {
			$tag = self::compose_code_tag( array( 'data-advanced-views-assets' => true ), $css_code );

			return array( $tag );
		}

		return array();
	}

	/**
	 * @return Html_Tag[]
	 */
	protected function compose_asset_tags(): array {
		$tags      = array();
		$css_codes = $this->assets_collector->get_css_code();

		foreach ( $css_codes as $name => $css_code ) {
			$asset_tag = $this->compose_asset_tag( $name, $css_code );

			if ( $asset_tag instanceof Html_Tag ) {
				$tags[] = $asset_tag;
			}
		}

		return $tags;
	}

	/**
	 * @return Html_Tag[]
	 */
	protected function compose_global_tags(): array {
		$global_styles = $this->tailwind_deduplicator->is_used() ?
			$this->tailwind_globals->get_global_styles() :
			'';
		$css_code      = Code_Minifier::minify_css( $global_styles );
		$is_filled     = strlen( $css_code ) > 0;

		if ( $is_filled ) {
			$tag = self::compose_code_tag( array( 'data-advanced-views-tailwind-global' => true ), $css_code );

			return array( $tag );
		}

		return array();
	}

	protected function compose_asset_tag( string $name, string $css_code ): ?Html_Tag {
		// the marker is a comment, so the check must precede the minification.
		$is_tailwind = Tailwind_Parser::is_tailwind( $css_code );
		$css_code    = Code_Minifier::minify_css( $css_code );

		$css_code = $is_tailwind ?
			$this->tailwind_deduplicator->strip_duplicate_rules( $css_code ) :
			$css_code;

		// can be empty after the minification or filtering.
		$is_filled = strlen( $css_code ) > 0;

		if ( $is_filled ) {
			return self::compose_code_tag( array( 'data-advanced-views-asset' => $name ), $css_code );
		}

		return null;
	}

	/**
	 * @param array<string,string|bool> $attrs
	 */
	protected static function compose_code_tag( array $attrs, string $code ): Html_Tag {
		$css = raw_html( $code );

		return tag( 'style' )
			->attributes( $attrs )
			->content( $css );
	}
}
