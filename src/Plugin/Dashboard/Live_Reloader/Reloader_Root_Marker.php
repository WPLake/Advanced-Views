<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader;

use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Reloader_Root_Marker {
	private Live_Reloader_Component $live_reloader_component;
	private Html_Renderer $html_renderer;

	public function __construct( Live_Reloader_Component $live_reloader_component, Html_Renderer $html_renderer ) {
		$this->live_reloader_component = $live_reloader_component;
		$this->html_renderer           = $html_renderer;
	}

	public function mark( string $all_css_code ): string {
		// introduce a styles variable, which allows to detect the styles root inside a webcomponent.
		if ( false === $this->live_reloader_component->is_active() ) {
			return $all_css_code;
		}

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

		return $this->html_renderer->render_html( $tags );
	}
}
