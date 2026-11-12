<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime;

use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Inline_Style_Injector;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

class Inline_Asset_Injector extends Actor_Base implements Actor {
	private Inline_Asset_Composer $asset_composer;
	private Inline_Style_Injector $style_injector;
	private Html_Renderer $html_renderer;

	public function __construct(
		Inline_Asset_Composer $assets_builder,
		Inline_Style_Injector $style_injector,
		Html_Renderer $html_renderer
	) {
		$this->asset_composer = $assets_builder;
		$this->style_injector = $style_injector;
		$this->html_renderer  = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// print_assets() contains ob_get_clean, so must be executed after all other scripts.
		self::add_action( 'wp_footer', array( $this, 'print_assets' ), 9999 );
	}

	public function print_assets(): void {
		$all_css_code = $this->render_tags( $this->asset_composer->compose_css() );
		$all_js_code  = $this->render_tags( $this->asset_composer->compose_js() );

		$this->asset_composer->reset();

		if ( 0 === strlen( $all_css_code ) &&
			0 === strlen( $all_js_code ) ) {
			$this->style_injector->release();

			return;
		}

		$this->style_injector->inject( $all_css_code );

		if ( strlen( $all_js_code ) > 0 ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $all_js_code;
		}
	}

	/**
	 * @param Html_Tag[] $tags
	 */
	protected function render_tags( array $tags ): string {
		if ( array() === $tags ) {
			return '';
		}

		$rendered_tags = array_map(
			fn( Html_Tag $tag ) => $this->html_renderer->render_html( $tag ),
			$tags
		);

		return sprintf( "\n%s\n", implode( "\n", $rendered_tags ) );
	}
}
