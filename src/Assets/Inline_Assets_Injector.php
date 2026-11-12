<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

use Org\Wplake\Advanced_Views\Assets\Rework\Inline_Assets_Builder;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Reloader_Root_Marker;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

class Inline_Assets_Injector extends Actor_Base implements Actor {
	private Inline_Assets_Builder $assets_builder;
	private Reloader_Root_Marker $reloader_root_marker;
	private Html_Renderer $html_renderer;
	private ?int $buffer_level;

	public function __construct( Inline_Assets_Builder $assets_builder, Reloader_Root_Marker $reloader_root_marker, Html_Renderer $html_renderer ) {
		$this->assets_builder       = $assets_builder;
		$this->reloader_root_marker = $reloader_root_marker;
		$this->html_renderer        = $html_renderer;
		$this->buffer_level         = null;
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
		$all_css_code = $this->render_tags( $this->assets_builder->compose_css() );
		$all_js_code  = $this->render_tags( $this->assets_builder->compose_js() );

		$this->assets_builder->reset();

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
			$custom_location_css = $this->reloader_root_marker->mark( $all_css_code );
			$page_content        = str_replace( '<!--advanced-views:styles/custom-location-->', $custom_location_css, $page_content );
			$all_css_code        = '';
		}

		$page_content = str_replace( '<!--advanced-views:styles-->', $all_css_code, $page_content );

		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $page_content;
	}
}
