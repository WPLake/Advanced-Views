<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post\Post_Type\Post_Selections\Integrations\Elementor;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;
use Org\Wplake\Advanced_Views\Post\Post_Type\Core\Integrations\Cpt_Item_Picker;

/**
 * The Post Selection Elementor widget's own editor/preview script enqueueing + localization - a standalone
 * Hookable (registered alongside Cpt_Widget_Registrar, not through it), since asset paths/localized var names are
 * the one piece of config that's genuinely per-CPT and doesn't belong hard-coded into that otherwise fully
 * generic class.
 */
final class Selection_Elementor_Assets extends Hookable implements Hooks_Interface {
	// prefixed by the plugin name for wp.org Plugin directory discover.
	const NAME = Plugin::PRODUCT_SLUG . '/post-selection-elementor';

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
		// deps on wp-api-fetch/wp-i18n for itemActionLinks.ts's "Refresh"; on elementor-editor so window.elementor
		// exists by the time this script runs (the panel/preview split - and why 'elementor-frontend' must NOT be
		// a dep of the preview-side script below - is a documented Elementor gotcha, see enqueue_preview_assets()).
		$this->enqueue_script( array( 'wp-api-fetch', 'wp-i18n', 'elementor-editor' ) );

		wp_localize_script(
			self::NAME,
			'avfSelectionElementor',
			array(
				'itemControlId' => 'selection_id',
				'itemPicker'    => $this->item_picker->get_js_data(),
			)
		);
	}

	public function enqueue_preview_assets(): void {
		// no 'elementor-frontend' dep here - Elementor has a documented load bug when a script enqueued via
		// 'elementor/preview/enqueue_scripts' depends on it. selectionElementor.ts waits on the
		// 'elementor/frontend/init' window event instead, the pattern Elementor's own docs recommend for this.
		$this->enqueue_script( array( 'jquery' ) );
	}

	/**
	 * @param string[] $deps
	 */
	protected function enqueue_script( array $deps ): void {
		wp_enqueue_script(
			self::NAME,
			$this->plugin->get_assets_url( 'js/admin/elementor/selection-elementor.min.js' ),
			$deps,
			$this->plugin->get_version(),
			true
		);
	}
}
