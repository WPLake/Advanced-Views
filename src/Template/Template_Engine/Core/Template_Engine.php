<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Core;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer;

defined( 'ABSPATH' ) || exit;

interface Template_Engine {
	public function get_name(): string;

	public function get_label(): string;

	public function create_renderer(): Template_Renderer;

	public function create_integration(): Template_Integration;

	public function create_token_factory(): Token_Factory;
}
