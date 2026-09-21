<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template_Engine\Blade;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Base\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Filesystem_Factory;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Integration\Template_Integration;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Rendering\Template_Renderer;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Template_Engine;

final class Blade_Template_Engine implements Template_Engine {
	private string $uploads_folder;
	private Logger $logger;
	private Settings_Storage $settings;

	public function __construct( string $uploads_folder, Logger $logger, Settings_Storage $settings ) {
		$this->uploads_folder = $uploads_folder;
		$this->logger         = $logger;
		$this->settings       = $settings;
	}

	public function get_name(): string {
		return 'blade';
	}

	public function get_label(): string {
		return __( 'Blade (requires PHP >= 8.2.0)', 'acf-views' );
	}

	public function create_renderer(): Template_Renderer {
		return new Blade_Renderer(
			$this->uploads_folder,
			$this->logger,
			$this->settings,
			WP_Filesystem_Factory::get_wp_filesystem()
		);
	}

	public function create_integration(): Template_Integration {
		return new Blade_Integration();
	}

	public function create_token_factory(): Token_Factory {
		return new Blade_Tokens();
	}
}
