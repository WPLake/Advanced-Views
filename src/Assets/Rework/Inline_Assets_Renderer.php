<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Loader;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Inline_Assets_Renderer {
	private Cpt_Assets_Collector $assets_collector;
	private Tailwind_Integrator $tailwind_integrator;
	private Style_Loader $style_loader;
	private Html_Renderer $html_renderer;

	public function __construct( Cpt_Assets_Collector $assets_collector, Tailwind_Integrator $tailwind_integrator, Style_Loader $style_loader, Html_Renderer $html_renderer ) {
		$this->assets_collector    = $assets_collector;
		$this->tailwind_integrator = $tailwind_integrator;
		$this->style_loader        = $style_loader;
		$this->html_renderer       = $html_renderer;
	}

	public function render_css(): string {
		$assets_css_code    = $this->style_loader->get_css_code();
		$has_assets_css     = strlen( $assets_css_code ) > 0;
		$all_css_code       = $has_assets_css ?
			$this->make_code_tag( 'style', array( 'data-advanced-views-assets' => true ), $assets_css_code ) :
		'';
		$counter            = 0;
		$is_tailwind_in_use = false;

		foreach ( $this->assets_collector->get_css_code() as $name => $css_code ) {
			if ( $this->assets_collector->is_tailwind_asset( $name ) ) {
				$is_tailwind_in_use = true;

				$css_code = $this->tailwind_integrator->merge( $css_code );

				// can be empty after merging.
				if ( 0 === strlen( $css_code ) ) {
					continue;
				}
			}

			$all_css_code .= 0 === $counter ?
				"\n" :
				'';

			$all_css_code .= $this->make_code_tag( 'style', array( 'data-advanced-views-asset' => $name ), $css_code );

			++$counter;
		}

		$this->tailwind_integrator->reset();
		$this->assets_collector->clear_tailwind_assets();

		if ( $is_tailwind_in_use ) {
			$global_tailwind_styles = $this->tailwind_integrator->get_global_styles();

			if ( strlen( $global_tailwind_styles ) > 0 ) {
				$all_css_code .= 0 === $counter ?
					"\n" :
					'';

				$all_css_code .= $this->make_code_tag( 'style', array( 'data-advanced-views-tailwind-global' => true ), $global_tailwind_styles );
			}
		}

		return $all_css_code;
	}

	public function render_js(): string {
		$all_js_code = '';
		$counter     = 0;

		foreach ( $this->assets_collector->get_js_code() as $name => $js_code ) {
			$all_js_code .= 0 === $counter ?
				"\n" :
				'';

			$all_js_code .= $this->make_code_tag(
				'script',
				array(
					'type'                      => 'module',
					'data-advanced-views-asset' => $name,
				),
				$js_code
			);

			++$counter;
		}

		return $all_js_code;
	}

	/**
	 * @param array<string,string|bool> $attrs
	 */
	protected function make_code_tag( string $tag, array $attrs, string $code ): string {
		$tags = tag( $tag )
			->attributes( $attrs )
			->content(
				raw_html( $code )
			);

		$code_html = $this->html_renderer->render_html( $tags );

		return sprintf( "%s\n", $code_html );
	}
}
