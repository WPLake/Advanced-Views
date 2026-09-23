<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Integration\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

final class Cpt_Renderer {
	private Shortcode_Renderer $shortcode;
	private Front_Assets $front_assets;
	private Cpt_Settings_Storage $settings_storage;
	private Plugin_Cpt $cpt;

	public function __construct(
		Shortcode_Renderer $shortcode,
		Front_Assets $front_assets,
		Cpt_Settings_Storage $settings_storage,
		Public_Cpt $cpt
	) {
		$this->shortcode        = $shortcode;
		$this->front_assets     = $front_assets;
		$this->settings_storage = $settings_storage;
		$this->cpt              = $cpt;
	}

	/**
	 * @param array<string,string> $attrs
	 */
	public function render( array $attrs ): string {
		return $this->shortcode->render_shortcode( $attrs );
	}

	/**
	 * @param array<string,string> $attrs
	 */
	public function render_preview( array $attrs, bool $inline_styles ): string {
		$unique_id    = string( $attrs, 'id' );
		$cpt_settings = $this->settings_storage->get( $unique_id );

		if ( $cpt_settings->isLoaded() ) {
			$html         = $this->render( $attrs );
			$trimmed_html = trim( $html );

			if ( strlen( $trimmed_html ) > 0 ) {
				return $inline_styles ?
					$this->make_style_tag( $cpt_settings ) . $html :
					$html;
			}

			$placeholder_message = __( 'No output to preview', 'acf-views' );

			return self::make_placeholder( $placeholder_message );
		}

		return $this->get_empty_preview_placeholder();
	}

	protected function get_empty_preview_placeholder(): string {
		// translators: %s is a singular post-type name, e.g. "Layout".
		$message       = __( 'Select a %s to see the preview', 'acf-views' );
		$singular_name = $this->cpt->labels()->singular_name();

		$label = sprintf( $message, $singular_name );

		return self::make_placeholder( $label );
	}

	/**
	 * Each render carries its own scoped 'data-avf-id' style tag rather than relying on Front_Assets' page-level
	 * printing, since a dynamic 'render_callback' (ServerSideRender, Elementor's AJAX re-render) has no such page
	 * to print into; the editor's own script relocates this tag into <head>, replacing any existing one with the
	 * same id.
	 */
	protected function make_style_tag( Cpt_Settings $cpt_settings ): string {
		// internal (e.g. shadow DOM) CSS is scoped to its own markup and inlined there instead.
		if ( ! $cpt_settings->is_css_internal() ) {
			$css_code     = $cpt_settings->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY );
			$minified_css = $this->front_assets->minify_code( $css_code, Front_Assets::MINIFY_TYPE_CSS );
			$unique_id    = $cpt_settings->get_unique_id();

			return sprintf(
				'<style data-avf-id="%s">%s</style>',
				esc_attr( $unique_id ),
				$minified_css
			);
		}

		return '';
	}

	protected static function make_placeholder( string $message ): string {
		return sprintf(
			'<p class="avf-cpt-block__placeholder">[%s]</p>',
			esc_html( $message )
		);
	}
}
