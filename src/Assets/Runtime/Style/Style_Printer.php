<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

class Style_Printer {
	private Style_Composer $style_composer;
	private Style_Injector $style_injector;
	private Html_Renderer $html_renderer;

	public function __construct(
		Style_Composer $style_composer,
		Style_Injector $style_injector,
		Html_Renderer $html_renderer
	) {
		$this->style_composer = $style_composer;
		$this->style_injector = $style_injector;
		$this->html_renderer  = $html_renderer;
	}

	public function print_styles(): void {
		$css_code = $this->render_tags( $this->style_composer->compose() );

		$this->style_composer->reset();

		if ( 0 === strlen( $css_code ) ) {
			$this->style_injector->release();

			return;
		}

		$this->style_injector->inject( $css_code );
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
