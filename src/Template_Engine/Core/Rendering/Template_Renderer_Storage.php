<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template_Engine\Core\Rendering;

defined( 'ABSPATH' ) || exit;

interface Template_Renderer_Storage {
	public function resolve_renderer( string $name ): ?Template_Renderer;
}
