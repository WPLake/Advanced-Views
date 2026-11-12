<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;

defined( 'ABSPATH' ) || exit;

final class Web_Component_Builder {
	public static function build_component_js( Cpt_Settings $cpt_settings, string $js_code ): string {
		$tag_name = $cpt_settings->get_tag_name();

		// dashes to camelCase.
		$component_name = $cpt_settings->is_web_component() ?
			preg_replace_callback(
				'/-([a-z0-9])/',
				fn( $matches ) => strtoupper( $matches[1] ),
				$tag_name
			) :
			null;

		if ( is_string( $component_name ) ) {
			$is_with_shadow_dom = Cpt_Settings::WEB_COMPONENT_SHADOW_DOM === $cpt_settings->web_component;
			$shadow_js_lines    = array(
				'var html=this.innerHTML;',
				'this.attachShadow({mode:"open"});',
				'this.shadowRoot.innerHTML=html;',
			);
			$box_shadow_js      = $is_with_shadow_dom ?
				implode( '', $shadow_js_lines ) :
				'';

			$imports = self::extract_imports( $js_code );

			$escaped_name = esc_html( $component_name );
			$js_lines     = array(
				$imports,
				sprintf( 'class %s extends HTMLElement{', $escaped_name ),
				'connectedCallback(){',
				'"loading"===document.readyState?document.addEventListener("DOMContentLoaded",this.setup.bind(this)):this.setup()',
				'}',
				sprintf( 'setup(){%s}', $box_shadow_js . $js_code ),
				'}',
				sprintf( 'customElements.define("%s", %s);', esc_html( $tag_name ), $escaped_name ),
			);

			return implode( '', $js_lines );
		}

		return $js_code;
	}

	/**
	 * The imports are cut out of the code (by reference), as they must be at the top of the component.
	 */
	protected static function extract_imports( string &$js_code ): string {
		$imports = '';

		preg_match_all( '/import [^;]+;/', $js_code, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$imports .= $match[0];
			$js_code  = str_replace( $match[0], '', $js_code );
		}

		return $imports;
	}
}
