<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Web_Component;

defined( 'ABSPATH' ) || exit;

final class JS_Component_Composer {
	public static function compose_classic_component( string $tag_name, string $js_code ): string {
		return self::compose_component( $tag_name, $js_code, '' );
	}

	public static function compose_shadow_component( string $tag_name, string $js_code ): string {
		$shadow_js_lines = array(
			'var html=this.innerHTML;',
			'this.attachShadow({mode:"open"});',
			'this.shadowRoot.innerHTML=html;',
		);

		return self::compose_component( $tag_name, $js_code, implode( '', $shadow_js_lines ) );
	}

	protected static function compose_component( string $tag_name, string $js_code, string $box_shadow_js ): string {
		// dashes to camelCase.
		$component_name = preg_replace_callback(
			'/-([a-z0-9])/',
			fn( $matches ) => strtoupper( $matches[1] ),
			$tag_name
		);

		if ( ! is_string( $component_name ) ) {
			return $js_code;
		}

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
