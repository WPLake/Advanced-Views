<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Assets_Output_Injector extends Actor_Base implements Actor {
	private Inline_Assets_Renderer $assets_renderer;
	private Live_Reloader_Component $live_reloader_component;
	private Html_Renderer $html_renderer;
	private ?int $buffer_level;

	public function __construct( Inline_Assets_Renderer $assets_renderer, Live_Reloader_Component $live_reloader_component, Html_Renderer $html_renderer ) {
		$this->assets_renderer         = $assets_renderer;
		$this->live_reloader_component = $live_reloader_component;
		$this->html_renderer           = $html_renderer;
		$this->buffer_level            = null;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// print_assets() contains ob_get_clean, so must be executed after all other scripts.
		self::add_action( 'wp_footer', array( $this, 'print_assets' ), 9999 );
		self::add_action( 'wp_head', array( $this, 'print_styles_stub' ) );
		// don't use 'get_header', as it doesn't work in blocks theme.
		self::add_action( 'template_redirect', array( $this, 'start_buffering' ) );
	}

	public function start_buffering(): void {
		ob_start();
		$this->buffer_level = ob_get_level();
	}

	public function print_styles_stub(): void {
		echo '<!--advanced-views:styles-->';
	}

	public function print_assets(): void {
		$all_css_code = $this->assets_renderer->render_css();
		$all_js_code  = $this->assets_renderer->render_js();

		if ( 0 === strlen( $all_css_code ) &&
			0 === strlen( $all_js_code ) ) {
			$this->flush_own_buffer();

			return;
		}

		if ( is_int( $this->buffer_level ) ) {
			$this->print_buffered_page( $all_css_code );
		} else {
			// if buffer_level is null, then 'template_redirect' hook with ob_start wasn't called,
			// so we must echo styles here, instead of the header.
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $all_css_code;
		}

		if ( strlen( $all_js_code ) > 0 ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $all_js_code;
		}
	}

	protected function flush_own_buffer(): void {
		// do not close the buffer, if it's not ours
		// (then ours will be closed automatically with the end of script execution).
		if ( is_int( $this->buffer_level ) &&
			ob_get_level() === $this->buffer_level ) {
			ob_end_flush();
		}
	}

	protected function print_buffered_page( string $all_css_code ): void {
		// close previous buffers. Some plugins may not close, if detect that ob_get_level() is another than was
		// e.g. 'lightbox-photoswipe'.
		while ( ob_get_level() > (int) $this->buffer_level ) {
			ob_end_flush();
		}

		$page_content = (string) ob_get_clean();

		$custom_location_position = strpos( $page_content, '<!--advanced-views:styles/custom-location-->' );

		if ( is_int( $custom_location_position ) ) {
			$custom_location_css = $this->compose_custom_location_css( $all_css_code );
			$page_content        = str_replace( '<!--advanced-views:styles/custom-location-->', $custom_location_css, $page_content );
			$all_css_code        = '';
		}

		$page_content = str_replace( '<!--advanced-views:styles-->', $all_css_code, $page_content );

		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $page_content;
	}

	protected function compose_custom_location_css( string $all_css_code ): string {
		// introduce a styles variable, which allows to detect the styles root inside a webcomponent.
		if ( false === $this->live_reloader_component->is_active() ) {
			return $all_css_code;
		}

		// the wrapper is necessary to make .parentElement work.
		$location_script = tag( 'script' )
			->content(
				raw_html( 'class AvfStylesLocation extends HTMLElement{connectedCallback(){"loading"===document.readyState?document.addEventListener("DOMContentLoaded",this.setup.bind(this)):this.setup()}setup(){window["avfStylesRoot"]=this.parentElement;}}customElements.define("avf-styles-location", AvfStylesLocation)' )
			);

		$tags = tag( 'div' )
			->attributes( array( 'hidden' => true ) )
			->content(
				raw_html( $all_css_code ),
				tag( 'avf-styles-location' ),
				$location_script
			);

		return $this->html_renderer->render_html( $tags );
	}
}
