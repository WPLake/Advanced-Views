<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Tokens\Twig_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Template_Engine;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;

class Twig_Template_Engine implements Template_Engine {
	const NAME = 'twig';

	protected Logger $logger;
	protected Settings_Storage $settings;
	protected Html_Renderer $html_renderer;

	public function __construct( Logger $logger, Settings_Storage $settings, Html_Renderer $html_renderer ) {
		$this->html_renderer = $html_renderer;
		$this->logger   = $logger;
		$this->settings = $settings;
	}

	public function get_name(): string {
		return self::NAME;
	}

	public function get_label(): string {
		return __( 'Twig', 'acf-views' );
	}

	public function create_renderer( string $render_folder ): Template_Renderer {
		return new Twig_Renderer(
			$render_folder,
			$this->logger,
			$this->settings,
			WP_Filesystem_Factory::get_wp_filesystem(),
			$this->html_renderer
		);
	}

	public function create_integration(): Template_Integration {
		return new Twig_Integration();
	}

	public function create_token_factory(): Token_Factory {
		return new Twig_Tokens( Twig_Printer::class );
	}
}
