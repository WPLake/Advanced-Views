<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Script;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

class Script_Renderer {
	private Script_Tag_Composer $script_composer;
	private Html_Renderer $html_renderer;

	public function __construct( Script_Tag_Composer $script_composer, Html_Renderer $html_renderer ) {
		$this->script_composer = $script_composer;
		$this->html_renderer   = $html_renderer;
	}

	public function print_scripts(): void {
		$js_code = $this->render_tags( $this->script_composer->compose_tags() );

		if ( strlen( $js_code ) > 0 ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $js_code;
		}
	}

	/**
	 * @param Html_Tag[] $tags
	 */
	protected function render_tags( array $tags ): string {
		if ( array() === $tags ) {
			return '';
		}

		$rendered_tags = array_map(
			fn( Html_Tag $tag ) => $this->html_renderer->render_html( $tag ),
			$tags
		);

		return sprintf( "\n%s\n", implode( "\n", $rendered_tags ) );
	}
}
