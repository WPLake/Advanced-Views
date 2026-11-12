<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Script;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

final class Script_Renderer {
	private Script_Tag_Composer $script_composer;
	private Html_Renderer $html_renderer;

	public function __construct( Script_Tag_Composer $script_composer, Html_Renderer $html_renderer ) {
		$this->script_composer = $script_composer;
		$this->html_renderer   = $html_renderer;
	}

	public function print_scripts(): void {
		$tags      = $this->script_composer->compose_tags();
		$js_code   = $this->render_tags( $tags );
		$is_filled = strlen( $js_code ) > 0;

		if ( $is_filled ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $js_code;
		}
	}

	/**
	 * @param Html_Tag[] $tags
	 */
	protected function render_tags( array $tags ): string {
		$is_filled = array() !== $tags;

		if ( $is_filled ) {
			$rendered_tags = array_map(
				fn( Html_Tag $tag ) => $this->html_renderer->render_html( $tag ),
				$tags
			);
			$joined_tags   = implode( "\n", $rendered_tags );

			return sprintf( "\n%s\n", $joined_tags );
		}

		return '';
	}
}
