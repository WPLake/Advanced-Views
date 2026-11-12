<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

use Org\Wplake\Advanced_Views\Assets\Rework\Inline_Assets_Builder;
use Org\Wplake\Advanced_Views\Assets\Rework\Page_Buffer;
use Org\Wplake\Advanced_Views\Assets\Rework\Inline_Style_Injector;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

class Inline_Assets_Injector extends Actor_Base implements Actor {
	private Inline_Assets_Builder $assets_builder;
	private Page_Buffer $page_buffer;
	private Inline_Style_Injector $style_injector;
	private Html_Renderer $html_renderer;

	public function __construct( Inline_Assets_Builder $assets_builder, Page_Buffer $page_buffer, Inline_Style_Injector $style_injector, Html_Renderer $html_renderer ) {
		$this->assets_builder = $assets_builder;
		$this->page_buffer    = $page_buffer;
		$this->style_injector = $style_injector;
		$this->html_renderer  = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// print_assets() contains ob_get_clean, so must be executed after all other scripts.
		self::add_action( 'wp_footer', array( $this, 'print_assets' ), 9999 );
		self::add_action( 'wp_head', array( $this, 'print_styles_stub' ) );
	}

	public function print_styles_stub(): void {
		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Inline_Style_Injector::STUB;
	}

	public function print_assets(): void {
		$all_css_code = $this->render_tags( $this->assets_builder->compose_css() );
		$all_js_code  = $this->render_tags( $this->assets_builder->compose_js() );

		$this->assets_builder->reset();

		if ( 0 === strlen( $all_css_code ) &&
			0 === strlen( $all_js_code ) ) {
			$this->page_buffer->flush_own();

			return;
		}

		if ( $this->page_buffer->is_started() ) {
			$page_content = $this->page_buffer->take_content();

			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->style_injector->place( $page_content, $all_css_code );
		} else {
			// if the buffer wasn't started, then 'template_redirect' hook wasn't called,
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
}
