<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Script;

use Org\Wplake\Advanced_Views\Assets\Runtime\Asset_Tag_Renderer;

defined( 'ABSPATH' ) || exit;

final class Script_Renderer {
	private Script_Tag_Provider $script_provider;
	private Asset_Tag_Renderer $tags_renderer;

	public function __construct( Script_Tag_Provider $script_provider, Asset_Tag_Renderer $tags_renderer ) {
		$this->script_provider = $script_provider;
		$this->tags_renderer   = $tags_renderer;
	}

	public function print_scripts(): void {
		$tags = $this->script_provider->resolve_tags();

		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->tags_renderer->render_tags( $tags );
	}
}
