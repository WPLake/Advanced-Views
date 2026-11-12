<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style;

use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Injector\Style_Injector;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Tailwind\Tailwind_Page_Styles;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

final class Style_Renderer {
	private Style_Tag_Composer $style_composer;
	private Style_Injector $style_injector;
	private Html_Renderer $html_renderer;
	private Tailwind_Page_Styles $tailwind_styles;

	public function __construct(
		Style_Tag_Composer $style_composer,
		Style_Injector $style_injector,
		Html_Renderer $html_renderer,
		Tailwind_Page_Styles $tailwind_styles
	) {
		$this->style_composer  = $style_composer;
		$this->style_injector  = $style_injector;
		$this->html_renderer   = $html_renderer;
		$this->tailwind_styles = $tailwind_styles;
	}

	public function print_styles(): void {
		$tags     = $this->style_composer->compose_tags();
		$css_code = $this->render_tags( $tags );

		$this->tailwind_styles->reset();

		$is_filled = strlen( $css_code ) > 0;

		if ( $is_filled ) {
			$this->style_injector->inject( $css_code );

			return;
		}

		$this->style_injector->release();
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
