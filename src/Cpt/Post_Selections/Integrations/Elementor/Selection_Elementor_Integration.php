<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt\Post_Selections\Integrations\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Cpt\Integrations\Cpt_Integration_Block;
use Org\Wplake\Advanced_Views\Cpt\Integrations\Elementor\Cpt_Elementor_Bridge;
use Org\Wplake\Advanced_Views\Cpt\Integrations\Elementor\Cpt_Elementor_Widget;
use Org\Wplake\Advanced_Views\Cpt\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Cpt\Post_Selections\Integrations\Gutenberg\Selection_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Cpt\Post_Selections\Integrations\Post_Selection_Shortcode;
use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;

final class Selection_Elementor_Integration extends Hookable implements Hooks_Interface {
	// prefixed by the plugin name for wp.org Plugin directory discover.
	const NAME = Plugin::PRODUCT_SLUG . '/post-selection-elementor';

	private Selection_Settings_Storage $selections_settings_storage;
	private Post_Selection_Shortcode $selection_shortcode;
	private Front_Assets $front_assets;
	private Plugin $plugin;
	private Public_Cpt $selection_cpt;

	public function __construct(
		Selection_Settings_Storage $selections_settings_storage,
		Post_Selection_Shortcode $selection_shortcode,
		Front_Assets $front_assets,
		Plugin $plugin,
		Public_Cpt $selection_cpt
	) {
		$this->selections_settings_storage = $selections_settings_storage;
		$this->selection_shortcode         = $selection_shortcode;
		$this->front_assets                = $front_assets;
		$this->plugin                      = $plugin;
		$this->selection_cpt               = $selection_cpt;
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		self::add_action(
			'elementor/elements/categories_registered',
			fn( Elements_Manager $elements_manager ) => Cpt_Elementor_Widget::add_category( $elements_manager )
		);
		self::add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );

		/**
		 * Unlike the 'elementor/...' hooks above, this one's underlying request isn't itself covered by
		 * is_admin_route() - Elementor's canvas preview loads through normal front-end template routing (see
		 * Cpt_Elementor_Bridge::is_editor_preview()'s docblock), so gating it the same way as the editor-only hooks
		 * below would mean it never fires. It's still safe to register unconditionally: the underlying WP hook only
		 * ever fires from within Elementor's own preview render, never on a plain front-end page view.
		 */
		self::add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );

		if ( $route_detector->is_admin_route() ) {
			self::add_action(
				'rest_api_init',
				fn() => register_rest_route(
					Plugin::REST_NAMESPACE,
					Selection_Gutenberg_Block::REST_ROUTE,
					Cpt_Integration_Block::get_items_list_rest_args( $this->selections_settings_storage ),
				)
			);
			self::add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor_assets' ) );
		}
	}

	public function register_widget( Widgets_Manager $widgets_manager ): void {
		$bridge = new Cpt_Elementor_Bridge(
			$this->selections_settings_storage,
			$this->selection_shortcode,
			$this->front_assets,
			$this->selection_cpt
		);

		Selection_Elementor_Widget::set_dependencies( $bridge );

		$widgets_manager->register( new Selection_Elementor_Widget() );
	}

	public function enqueue_editor_assets(): void {
		// deps on wp-api-fetch/wp-i18n for itemActionLinks.ts's "Refresh"; on elementor-editor so window.elementor
		// exists by the time this script runs (the panel/preview split - and why 'elementor-frontend' must NOT be
		// a dep of the preview-side script below - is a documented Elementor gotcha, see enqueue_preview_assets()).
		$this->enqueue_script( array( 'wp-api-fetch', 'wp-i18n', 'elementor-editor' ) );

		wp_localize_script(
			self::NAME,
			'avfSelectionElementor',
			array_merge(
				array( 'itemControlId' => 'selection_id' ),
				Cpt_Integration_Block::get_localized_item_picker_data(
					$this->selections_settings_storage,
					$this->selection_cpt,
					Selection_Gutenberg_Block::REST_ROUTE
				)
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
			$this->plugin->get_assets_url( 'admin/js/elementor/selection-elementor.min.js' ),
			$deps,
			$this->plugin->get_version(),
			true
		);
	}
}
