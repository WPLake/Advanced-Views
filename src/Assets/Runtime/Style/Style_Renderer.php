<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Style;

use Org\Wplake\Advanced_Views\Assets\Runtime\Asset_Tag_Renderer;
use Org\Wplake\Advanced_Views\Assets\Runtime\Style\Injector\Style_Injector;

defined( 'ABSPATH' ) || exit;

final class Style_Renderer {
	private Style_Tag_Provider $style_provider;
	private Style_Injector $style_injector;
	private Asset_Tag_Renderer $tags_renderer;

	public function __construct(
		Style_Tag_Provider $style_provider,
		Style_Injector $style_injector,
		Asset_Tag_Renderer $tags_renderer
	) {
		$this->style_provider = $style_provider;
		$this->style_injector = $style_injector;
		$this->tags_renderer  = $tags_renderer;
	}

	public function print_styles(): void {
		$tags     = $this->style_provider->resolve_tags();
		$css_code = $this->tags_renderer->render_tags( $tags );

		$this->style_injector->inject( $css_code );
	}
}
