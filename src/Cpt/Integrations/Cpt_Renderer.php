<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt\Integrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;

/**
 * Render logic shared by every editor integration backed by a Shortcode_Renderer (Layout, Post Selection...) -
 * one instance per CPT (constructed with that CPT's concrete Shortcode_Renderer), sitting under
 * Cpt_Gutenberg_Block/Cpt_Elementor_Widget the same way Cpt_Item_Picker sits under them for item-picking.
 */
final class Cpt_Renderer {
	private Shortcode_Renderer $shortcode;
	private Front_Assets $front_assets;

	public function __construct( Shortcode_Renderer $shortcode, Front_Assets $front_assets ) {
		$this->shortcode    = $shortcode;
		$this->front_assets = $front_assets;
	}

	/**
	 * @param array<string,string> $attrs
	 */
	public function render( array $attrs ): string {
		return $this->shortcode->render_shortcode( $attrs );
	}

	/**
	 * render(), wrapped in the item's own scoped <style>, or a "no output" placeholder if rendering produced
	 * nothing - callers must confirm the item resolves (Cpt_Settings::isLoaded()) before calling this; use
	 * get_empty_preview_placeholder() for the "nothing chosen yet" case instead.
	 *
	 * @param array<string,string> $attrs
	 */
	public function render_preview( Cpt_Settings $cpt_settings, array $attrs ): string {
		$html = $this->render( $attrs );

		if ( strlen( trim( $html ) ) > 0 ) {
			return $this->make_style_tag( $cpt_settings ) . $html;
		}

		return self::make_placeholder( __( 'No output to preview', 'acf-views' ) );
	}

	public function get_empty_preview_placeholder( string $singular_name ): string {
		$label = sprintf(
		// translators: %s is a singular post-type name, e.g. "Layout".
			__( 'Select a %s to see the preview', 'acf-views' ),
			$singular_name
		);

		return self::make_placeholder( $label );
	}

	/**
	 * Every render carries its own scoped 'data-avf-id' style tag, instead of relying solely on
	 * Front_Assets' page-level 'wp_head'/'wp_footer' printing - a dynamic 'render_callback' can't reach
	 * that when rendered through the block editor's ServerSideRender REST call (or Elementor's own AJAX
	 * per-widget re-render), which is a request of its own with no such page to print into.
	 *
	 * Each editor's own editor-only script then moves this tag into <head>, replacing any existing tag with
	 * the same id, so repeated/updated uses of the same item in the editor don't keep accumulating duplicate CSS.
	 */
	public function make_style_tag( Cpt_Settings $cpt_settings ): string {
		// internal (e.g. shadow DOM) CSS is scoped to its own markup and inlined there instead.
		if ( ! $cpt_settings->is_css_internal() ) {
			$css = $this->front_assets->minify_code(
				$cpt_settings->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY ),
				Front_Assets::MINIFY_TYPE_CSS
			);

			return sprintf(
				'<style data-avf-id="%s">%s</style>',
				esc_attr( $cpt_settings->get_unique_id() ),
				$css
			);
		}

		return '';
	}

	protected static function make_placeholder( string $message ): string {
		return sprintf( '<p class="avf-cpt-block__placeholder">[%s]</p>', esc_html( $message ) );
	}
}
