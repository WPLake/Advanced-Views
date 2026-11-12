<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime;

use Org\Wplake\Advanced_Views\Assets\Rework\Cpt_Assets_Collector;
use Org\Wplake\Advanced_Views\Assets\Rework\Tailwind_Integrator;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Collector;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Inline_Assets_Builder {
	private Cpt_Assets_Collector $assets_collector;
	private Tailwind_Integrator $tailwind_integrator;
	private Style_Collector $style_collector;

	public function __construct(
		Cpt_Assets_Collector $assets_collector,
		Tailwind_Integrator $tailwind_integrator,
		Style_Collector $style_collector
	) {
		$this->assets_collector    = $assets_collector;
		$this->tailwind_integrator = $tailwind_integrator;
		$this->style_collector     = $style_collector;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function compose_css(): array {
		$tags            = array();
		$assets_css_code = $this->style_collector->get_css_code();

		if ( strlen( $assets_css_code ) > 0 ) {
			$tags[] = $this->compose_code_tag( 'style', array( 'data-advanced-views-assets' => true ), $assets_css_code );
		}

		$is_tailwind_in_use = false;

		foreach ( $this->assets_collector->get_css_code() as $name => $css_code ) {
			if ( $this->tailwind_integrator->is_tailwind_asset( $name ) ) {
				$is_tailwind_in_use = true;

				$css_code = $this->tailwind_integrator->merge( $css_code );

				// can be empty after merging.
				if ( 0 === strlen( $css_code ) ) {
					continue;
				}
			}

			$tags[] = $this->compose_code_tag( 'style', array( 'data-advanced-views-asset' => $name ), $css_code );
		}

		$global_tailwind_styles = $is_tailwind_in_use ?
			$this->tailwind_integrator->get_global_styles() :
			'';

		if ( strlen( $global_tailwind_styles ) > 0 ) {
			$tags[] = $this->compose_code_tag( 'style', array( 'data-advanced-views-tailwind-global' => true ), $global_tailwind_styles );
		}

		return $tags;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function compose_js(): array {
		$tags = array();

		foreach ( $this->assets_collector->get_js_code() as $name => $js_code ) {
			$attributes = array(
				'type'                      => 'module',
				'data-advanced-views-asset' => $name,
			);

			$tags[] = $this->compose_code_tag( 'script', $attributes, $js_code );
		}

		return $tags;
	}

	/**
	 * Drops the page-wide Tailwind state, which is no longer needed once the assets are composed.
	 */
	public function reset(): void {
		$this->tailwind_integrator->reset();
	}

	/**
	 * @param array<string,string|bool> $attrs
	 */
	protected function compose_code_tag( string $tag, array $attrs, string $code ): Html_Tag {
		return tag( $tag )
			->attributes( $attrs )
			->content(
				raw_html( $code )
			);
	}
}
