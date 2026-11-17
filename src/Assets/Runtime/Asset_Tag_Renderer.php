<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

final class Asset_Tag_Renderer {
	private Html_Renderer $html_renderer;

	public function __construct( Html_Renderer $html_renderer ) {
		$this->html_renderer = $html_renderer;
	}

	/**
	 * @param Html_Tag[] $tags
	 */
	public function render_tags( array $tags ): string {
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
