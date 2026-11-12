<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Assets\Rework\Code_Minifier;
use Org\Wplake\Advanced_Views\Assets\Rework\Tailwind_Integrator;
use Org\Wplake\Advanced_Views\Assets\Rework\Web_Component_Builder;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Loader;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Loader;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Front_Assets extends Actor_Base implements Actor {
	private Script_Loader $script_loader;
	private ?int $buffer_level;
	private bool $is_custom_interactivity_api_import_map_required;
	/**
	 * @var array<string, string>
	 */
	private array $inline_js_code;
	/**
	 * @var array<string, string>
	 */
	private array $include_css_code;
	private Style_Loader $style_loader;
	private Live_Reloader_Component $live_reloader_component;
	/**
	 * @var array<string,true>
	 */
	private array $tailwind_asset_ids;
	private Tailwind_Integrator $tailwind_merger;
	protected Html_Renderer $html_renderer;
	private Library_Container $library_container;

	public function __construct( Asset_Resolver $asset_resolver, Live_Reloader_Component $live_reloader_component, Html_Renderer $html_renderer, Style_Builder $style_builder, Library_Container $library_container, Tailwind_Integrator $tailwind_merger ) {
		$this->script_loader                                   = new Script_Loader( $asset_resolver );
		$this->buffer_level                                    = null;
		$this->is_custom_interactivity_api_import_map_required = false;

		$this->inline_js_code          = array();
		$this->include_css_code        = array();
		$this->live_reloader_component = $live_reloader_component;
		$this->style_loader            = new Style_Loader( $asset_resolver, $style_builder );
		$this->tailwind_asset_ids      = array();
		$this->tailwind_merger         = $tailwind_merger;
		$this->html_renderer           = $html_renderer;
		$this->library_container       = $library_container;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return ! $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_filter(
			'script_module_loader_src',
			array( $this, 'catch_interactivity_api_script_url' ),
			10,
			2
		);
		self::add_action( 'wp_footer', array( $this, 'enqueue_assets' ) );
		// printCustomAssets() contains ob_get_clean, so must be executed after all other scripts.
		self::add_action( 'wp_footer', array( $this, 'print_assets' ), 9999 );
		self::add_action( 'wp_head', array( $this, 'print_styles_stub' ) );
		// don't use 'get_header', as it doesn't work in blocks theme.
		self::add_action( 'template_redirect', array( $this, 'start_buffering' ) );
	}

	protected static function print_interactivity_api_import_map( string $interactivity_api_script_url ): void {
		$imports   = array(
			'@wordpress/interactivity' => $interactivity_api_script_url,
		);
		$data      = array(
			'imports' => $imports,
		);
		$json_data = (string) wp_json_encode(
			$data,
			JSON_HEX_TAG | JSON_HEX_AMP
		);

		$attributes = array(
			'type' => 'importmap',
			'id'   => 'avf-importmap',
		);

		wp_print_inline_script_tag( $json_data, $attributes );
	}

	public function start_buffering(): void {
		ob_start();
		$this->buffer_level = ob_get_level();
	}

	public function print_styles_stub(): void {
		echo '<!--advanced-views:styles-->';
	}

	public function add_asset( Cpt_Settings $cpt_settings ): void {
		$unique_id  = $cpt_settings->get_unique_id();
		$css_source = $cpt_settings->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY );
		$js_source  = $cpt_settings->get_js_code();

		$css_code = Code_Minifier::minify_css( $css_source );
		$js_code  = Code_Minifier::minify_js( $js_source );

		if ( strlen( $css_code ) > 0 &&
			false === $cpt_settings->is_css_internal() ) {
			$this->include_css_code[ $unique_id ] = $css_code;

			$this->mark_tailwind_asset( $unique_id, $css_source );
		}

		if ( strlen( $js_code ) > 0 ) {
			if ( $cpt_settings->is_wp_interactivity_in_use() ) {
				$this->is_custom_interactivity_api_import_map_required = true;
			}

			$this->inline_js_code[ $unique_id ] = Web_Component_Builder::build_component_js( $cpt_settings, $js_code );
		}

		foreach ( $this->library_container->get_all() as $pattern ) {
			if ( $pattern instanceof Layout_Library &&
				$cpt_settings instanceof Layout_Settings ) {
				$pattern->maybe_activate_layout( $cpt_settings );
			}

			if ( $pattern instanceof Selection_Library &&
				$cpt_settings instanceof Post_Selection_Settings ) {
				$pattern->maybe_activate_selection( $cpt_settings );
			}
		}
	}

	public function print_assets(): void {
		$assets_css_code    = $this->style_loader->get_css_code();
		$has_assets_css     = strlen( $assets_css_code ) > 0;
		$all_js_code        = '';
		$all_css_code       = $has_assets_css ?
			$this->make_code_tag( 'style', array( 'data-advanced-views-assets' => true ), $assets_css_code ) :
		'';
		$counter            = 0;
		$is_tailwind_in_use = false;

		foreach ( $this->include_css_code as $name => $css_code ) {
			if ( key_exists( $name, $this->tailwind_asset_ids ) ) {
				$is_tailwind_in_use = true;

				$css_code = $this->tailwind_merger->merge( $css_code );

				// can be empty after merging.
				if ( 0 === strlen( $css_code ) ) {
					continue;
				}
			}

			$all_css_code .= 0 === $counter ?
				"\n" :
				'';

			$all_css_code .= $this->make_code_tag( 'style', array( 'data-advanced-views-asset' => $name ), $css_code );

			++$counter;
		}

		$this->tailwind_merger->reset();
		$this->tailwind_asset_ids = array();

		if ( $is_tailwind_in_use ) {
			$global_tailwind_styles = $this->tailwind_merger->get_global_styles();

			if ( strlen( $global_tailwind_styles ) > 0 ) {
				$all_css_code .= 0 === $counter ?
					"\n" :
					'';

				$all_css_code .= $this->make_code_tag( 'style', array( 'data-advanced-views-tailwind-global' => true ), $global_tailwind_styles );
			}
		}

		$counter = 0;
		foreach ( $this->inline_js_code as $name => $js_code ) {
			$all_js_code .= 0 === $counter ?
				"\n" :
				'';

			$all_js_code .= $this->make_code_tag(
				'script',
				array(
					'type'                      => 'module',
					'data-advanced-views-asset' => $name,
				),
				$js_code
			);

			++$counter;
		}

		if ( 0 === strlen( $all_css_code ) &&
			0 === strlen( $all_js_code ) ) {
			// do not close the buffer, if it's not ours
			// (then ours will be closed automatically with the end of script execution).
			if ( is_int( $this->buffer_level ) &&
				ob_get_level() === $this->buffer_level ) {
				ob_end_flush();
			}

			return;
		}

		if ( is_int( $this->buffer_level ) ) {
			// close previous buffers. Some plugins may not close, if detect that ob_get_level() is another than was
			// e.g. 'lightbox-photoswipe'.
			while ( ob_get_level() > $this->buffer_level ) {
				ob_end_flush();
			}

			$page_content = (string) ob_get_clean();

			$custom_location_position = strpos( $page_content, '<!--advanced-views:styles/custom-location-->' );

			if ( is_int( $custom_location_position ) ) {
				// introduce a styles variable, which allows to detect the styles root inside a webcomponent.
				if ( $this->live_reloader_component->is_active() ) {
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

					$all_css_code = $this->html_renderer->render_html( $tags );
				}

				$page_content = str_replace( '<!--advanced-views:styles/custom-location-->', $all_css_code, $page_content );
				$all_css_code = '';
			}

			$page_content = str_replace( '<!--advanced-views:styles-->', $all_css_code, $page_content );

			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $page_content;
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

	public function enqueue_assets(): void {
		// enqueue wp interactivity, as in non-block themes it isn't enqueued by default.
		if ( $this->is_custom_interactivity_api_import_map_required &&
			function_exists( 'wp_enqueue_script_module' ) ) {
			wp_enqueue_script_module( '@wordpress/interactivity' );
		}

		foreach ( $this->library_container->get_all() as $pattern ) {
			$script_provider = $pattern->get_js_library();
			$style_provider  = $pattern->get_css_library();

			if ( $script_provider instanceof Script_Provider &&
				$script_provider->has_active_scripts() ) {
				$this->script_loader->enqueue_scripts( $script_provider );
			}

			if ( $style_provider instanceof Style_Provider &&
				$style_provider->has_active_styles() ) {
				$name = $pattern->get_name();

				$this->style_loader->enqueue_styles( $name, $style_provider );
			}
		}
	}

	/**
	 * Classic themes (WP 6.7+) have no way to enqueue the iApi script and get its import map printed,
	 * and a fixed url can't be used, as iApi script urls are versioned
	 * (see wp-includes/assets/script-modules-packages.min.php).
	 */
	public function catch_interactivity_api_script_url( string $src, string $id ): string {
		if ( '@wordpress/interactivity' === $id &&
			$this->is_custom_interactivity_api_import_map_required ) {
			$this->is_custom_interactivity_api_import_map_required = false;

			self::print_interactivity_api_import_map( $src );
		}

		return $src;
	}

	protected function mark_tailwind_asset( string $unique_id, string $css_source ): void {
		if ( $this->tailwind_merger->is_tailwind( $css_source ) ) {
			$this->tailwind_asset_ids[ $unique_id ] = true;

			return;
		}

		unset( $this->tailwind_asset_ids[ $unique_id ] );
	}

	/**
	 * @param array<string,string|bool> $attrs
	 */
	protected function make_code_tag( string $tag, array $attrs, string $code ): string {
		$tags = tag( $tag )
			->attributes( $attrs )
			->content(
				raw_html( $code )
			);

		$code_html = $this->html_renderer->render_html( $tags );

		return sprintf( "%s\n", $code_html );
	}
}
