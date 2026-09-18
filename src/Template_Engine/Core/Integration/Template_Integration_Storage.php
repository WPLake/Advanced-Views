<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template_Engine\Core\Integration;

defined( 'ABSPATH' ) || exit;

interface Template_Integration_Storage {
	/**
	 * @return array<string,Template_Integration>
	 */
	public function get_integrations(): array;

	public function resolve_integration( string $template_engine ): ?Template_Integration;
}
