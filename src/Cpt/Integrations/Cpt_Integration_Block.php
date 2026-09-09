<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt\Integrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Cpt\Base\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Base\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;

/**
 * Stateless logic shared by every editor integration backed by a Cpt_Settings_Storage (Layout, Post Selection...) -
 * both Gutenberg (Cpt_Gutenberg_Block) and Elementor (Cpt_Elementor_Widget/Cpt_Elementor_Bridge) sit on top of this,
 * instead of one editor's integration depending on the other's.
 */
final class Cpt_Integration_Block {
	const CATEGORY = 'advanced-views';

	private function __construct() {
	}

	public static function render_preview( Cpt_Settings $cpt_settings, Front_Assets $assets, string $html ): string {
		$has_output = strlen( trim( $html ) ) > 0;

		if ( $has_output ) {
			$style_tag = self::make_style_tag( $cpt_settings, $assets );

			return $style_tag . $html;
		}

		$label = __( 'No output to preview', 'acf-views' );

		return self::make_placeholder( $label );
	}

	public static function get_empty_preview_placeholder( string $singular_name ): string {
		$label = sprintf(
		// translators: %s is a singular post-type name, e.g. "Layout".
			__( 'Select a %s to see the preview', 'acf-views' ),
			$singular_name
		);

		return self::make_placeholder( $label );
	}

	/**
	 * Keyed by the item's full unique id - not the short id used in legacy hand-typed shortcodes - so
	 * consumers (the block editor's SelectControl, Elementor's SELECT2 options, the "Refresh" REST route) never
	 * need to add/strip a CPT-specific prefix themselves.
	 *
	 * @return array<string,array{title:string,editUrl:string}>
	 */
	public static function get_items_list( Cpt_Settings_Storage $settings_storage ): array {
		$list = array();

		foreach ( $settings_storage->get_unique_id_with_name_items_list() as $unique_id => $title ) {
			$list[ $unique_id ] = array(
				'title'   => $title,
				'editUrl' => $settings_storage->get( $unique_id )->get_edit_post_link( 'raw' ),
			);
		}

		return $list;
	}

	/**
	 * The item-picker fields both editors' "Add new / Edit / Refresh" JS localizes (see AvfCptItemPickerData in
	 * global.d.ts) - identical for Gutenberg's and Elementor's own enqueue_editor_assets(), so it's built once here
	 * instead of separately per editor per CPT.
	 *
	 * @return array{items:array<string,array{title:string,editUrl:string}>,newItemUrl:string,itemsRestUrl:string,canManage:bool,itemLabel:string}
	 */
	public static function get_localized_item_picker_data(
		Cpt_Settings_Storage $settings_storage,
		Public_Cpt $cpt,
		string $rest_route
	): array {
		return array(
			'items'        => self::get_items_list( $settings_storage ),
			'newItemUrl'   => admin_url( sprintf( 'post-new.php?post_type=%s', $cpt->cpt_name() ) ),
			'itemsRestUrl' => sprintf( '/%s/%s', Plugin::REST_NAMESPACE, $rest_route ),
			'canManage'    => Avf_User::can_manage(),
			'itemLabel'    => $cpt->labels()->singular_name(),
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function get_items_list_rest_args( Cpt_Settings_Storage $settings_storage ): array {
		return array(
			'methods'             => 'GET',
			'permission_callback' => fn(): bool => Avf_User::can_manage(),
			/**
			 * @return array<string,array{title:string,editUrl:string}>
			 */
			'callback'            => fn(): array => self::get_items_list( $settings_storage ),
		);
	}

	protected static function make_placeholder( string $message ): string {
		return sprintf( '<p class="avf-cpt-block__placeholder">[%s]</p>', esc_html( $message ) );
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
	protected static function make_style_tag( Cpt_Settings $cpt_settings, Front_Assets $front_assets ): string {
		// internal (e.g. shadow DOM) CSS is scoped to its own markup and inlined there instead.
		if ( ! $cpt_settings->is_css_internal() ) {
			$css = $front_assets->minify_code(
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
}
