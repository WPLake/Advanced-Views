<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Libraries;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Active_Libraries;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Assets_Enqueuer;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Assets_Handles;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Assets_Location;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Code_Piece;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library_Assets;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library_Code;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library_Structure;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Structure;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Target;

class Light_Gallery_Library implements Library_Assets, Library_Code, Library_Structure {
	const NAME = 'light-gallery';

	protected Assets_Location $assets_location;

	public function __construct( Assets_Location $assets_location ) {
		$this->assets_location = $assets_location;
	}

	public function get_name(): string {
		return static::NAME;
	}

	public function get_auto_discover_name(): string {
		return 'light-gallery';
	}

	public function is_web_component_required(): bool {
		return true;
	}

	public function get_handles(): Assets_Handles {
		// the thumbnail addon is always in use.
		return new Assets_Handles(
			array( 'lightgallery', 'lg-thumbnail' ),
			array( 'lightgallery', 'lg-thumbnail' )
		);
	}

	public function enqueue_active( Active_Libraries $active_libraries ): string {
		$handles  = $active_libraries->get_handles( static::NAME );
		$css_code = Assets_Enqueuer::enqueue( $this->assets_location, $handles );

		// font and image paths in CSS won't work, as CSS will be added right to the page,
		// replacing with the related installation path avoids it.
		$assets_url              = $this->assets_location->url( '' );
		$relative_asset_url_base = Plugin::make_url_relative( $assets_url );
		$url_replacement         = sprintf( 'url(%s', $relative_asset_url_base );

		return str_replace( 'url(../', $url_replacement, $css_code );
	}

	public function generate_code( Target $target ): Code_Piece {
		$css_code = static::generate_css_code( $target );
		$js_code  = static::generate_js_code( $target );

		return new Code_Piece( $css_code, $js_code );
	}

	public function get_structure( Target $target ): Structure {
		if ( $target->is_multiple ) {
			$item_attrs = static::get_data_attrs( $target->item_prefix );
			$wrappers   = array(
				Structure::ROLE_LIST => new Html_Wrapper( 'ul' ),
				Structure::ROLE_ITEM => new Html_Wrapper( 'li', $item_attrs ),
			);

			return Structure::host( $wrappers );
		}

		$container_attrs = static::get_data_attrs( $target->field_id );
		$wrappers        = array(
			Structure::ROLE_CONTAINER => new Html_Wrapper( 'div', $container_attrs ),
		);

		return Structure::guest( $wrappers );
	}

	protected static function generate_js_code( Target $target ): string {
		$is_image = ! $target->is_multiple;

		$code  = "\t/* https://www.lightgalleryjs.com/docs/settings/#lightgallery-core */\n";
		$code .= sprintf( "\tnew lightGallery(%s, {\n", esc_html( $target->var_name ) );
		$code .= $is_image ?
			"\t\tselector: 'this',\n" :
			'';
		$code .= "\t\tcloseOnTap: true,\n";
		$code .= sprintf( "\t\tcounter: %s,\n", $is_image ? 'false' : 'true' );
		$code .= "\t\tdownload: false,\n";
		$code .= "\t\tallowMediaOverlap: false,\n";
		$code .= "\t\tenableDrag: false,\n";
		$code .= $is_image ?
			'' :
			"\t\tplugins: [window.lgThumbnail,],\n";

		return $code . "\t});";
	}

	protected static function generate_css_code( Target $target ): string {
		$selector = esc_html( $target->css_selector );

		$code = $target->is_multiple ?
			sprintf( "%s {\n\tlist-style: none;\n}\n\n", $selector ) :
			'';

		$hover_rule = sprintf( "%s img:hover {\n\tcursor: zoom-in;\n}", $selector );

		return $code . $hover_rule;
	}

	/**
	 * @return array<string,string>
	 */
	protected static function get_data_attrs( string $template_var ): array {
		return array(
			'data-src'      => sprintf( '{{ %s.full_size }}', $template_var ),
			'data-sub-html' => sprintf( '{{ %s.caption }}', $template_var ),
		);
	}
}
