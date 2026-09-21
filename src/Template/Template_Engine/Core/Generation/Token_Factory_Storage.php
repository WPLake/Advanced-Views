<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation;

defined( 'ABSPATH' ) || exit;

interface Token_Factory_Storage {
	public function resolve_token_factory( string $template_engine ): Token_Factory;
}
