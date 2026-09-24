<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Elementor;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;

/**
 * The Post Selection Elementor widget's own editor/preview script enqueueing + localization - a standalone
 * Hookable (registered alongside Cpt_Widget_Registrar, not through it), since asset paths/localized var names are
 * the one piece of config that's genuinely per-CPT and doesn't belong hard-coded into that otherwise fully
 * generic class.
 */
final class Selection_Elementor_Assets extends Hookable implements Hooks_Interface {
	const EDITOR_NAME  = Plugin::PRODUCT_SLUG . '/post-selection-elementor-editor';
	const PREVIEW_NAME = Plugin::PRODUCT_SLUG . '/post-selection-elementor-preview';

	private Cpt_Item_Picker $item_picker;
	private Plugin $plugin;

	public function __construct( Cpt_Item_Picker $item_picker, Plugin $plugin ) {
		$this->item_picker = $item_picker;
		$this->plugin      = $plugin;
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		/**
		 * This request isn't itself covered by is_admin_route() - Elementor's canvas preview loads through normal
		 * front-end template routing (see Cpt_Elementor_Widget::is_editor_preview()'s docblock), so gating it the
		 * same way as the editor-only hook below would mean it never fires. It's still safe to register
		 * unconditionally: the underlying WP hook only ever fires from within Elementor's own preview render,
		 * never on a plain front-end page view.
		 */
		self::add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );

		if ( $route_detector->is_admin_route() ) {
			self::add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor_assets' ) );
		}
	}

	public function enqueue_editor_assets(): void {
		// deps on wp-api-fetch/wp-i18n for actionLinksEditor.ts's "Refresh"; on elementor-editor so window.elementor
		// exists by the time this script runs (the panel/preview split - and why 'elementor-frontend' must NOT be
		// a dep of the preview-side script below - is a documented Elementor gotcha, see enqueue_preview_assets()).
		$script_url = $this->plugin->get_assets_url(
			'js/admin/post-type/post-selections/elementor/selection-elementor-editor.min.js'
		);
		$version    = $this->plugin->get_version();

		wp_enqueue_script(
			self::EDITOR_NAME,
			$script_url,
			array( 'wp-api-fetch', 'wp-i18n', 'elementor-editor' ),
			$version,
			true
		);

		wp_localize_script(
			self::EDITOR_NAME,
			'avfSelectionElementor',
			array(
				'itemControlId' => 'selection_id',
				'itemPicker'    => $this->item_picker->get_js_data(),
			)
		);
	}

	public function enqueue_preview_assets(): void {
		// no 'elementor-frontend' dep here - Elementor has a documented load bug when a script enqueued via
		// 'elementor/preview/enqueue_scripts' depends on it. selectionElementorPreview.ts waits on the
		// 'elementor/frontend/init' window event instead, the pattern Elementor's own docs recommend for this.
		$script_url = $this->plugin->get_assets_url(
			'js/admin/post-type/post-selections/elementor/selection-elementor-preview.min.js'
		);
		$version    = $this->plugin->get_version();

		wp_enqueue_script(
			self::PREVIEW_NAME,
			$script_url,
			array( 'jquery' ),
			$version,
			true
		);
	}
}
