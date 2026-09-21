<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\PHP;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Base\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Template_Engine;

final class PHP_Template_Engine implements Template_Engine {
	const NAME = 'php';
	private Logger $logger;
	private Settings_Storage $settings;

	public function __construct( Logger $logger, Settings_Storage $settings ) {
		$this->logger   = $logger;
		$this->settings = $settings;
	}

	public function get_name(): string {
		return self::NAME;
	}

	public function get_label(): string {
		return __( 'PHP', 'acf-views' );
	}

	public function create_renderer(): Template_Renderer {
		return new PHP_Renderer( $this->logger, $this->settings );
	}

	public function create_integration(): Template_Integration {
		return new PHP_Integration();
	}

	public function create_token_factory(): Token_Factory {
		return new PHP_Tokens();
	}
}
