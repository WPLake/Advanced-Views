<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template_Engine\Engines;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template_Engine\Core\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Integration\Template_Integration;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Integration\Template_Integration_Storage;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Rendering\File_Template_Renderer_Base;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Rendering\Template_Renderer;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use Org\Wplake\Advanced_Views\Template_Engine\Core\Template_Engine;

class Engines_Storage implements
	Template_Integration_Storage,
	Token_Factory_Storage,
	Template_Renderer_Storage {
	/**
	 * @var array<string, Template_Engine>
	 */
	private array $engines;
	/**
	 * @var array<string, Template_Renderer|null>
	 */
	private array $renderers;
	/**
	 * @var array<string, Token_Factory>
	 */
	private array $token_factories;
	/**
	 * @var array<string, Template_Integration>
	 */
	private array $integrations;

	/**
	 * @param Template_Engine[] $engines
	 */
	public function __construct( array $engines ) {
		$this->engines         = array();
		$this->renderers       = array();
		$this->token_factories = array();
		$this->integrations    = array();

		foreach ( $engines as $engine ) {
			$this->engines[ $engine->get_name() ] = $engine;
		}
	}

	public function resolve_renderer( string $name ): ?Template_Renderer {
		if ( ! key_exists( $name, $this->renderers ) ) {
			$this->renderers[ $name ] = $this->make_renderer( $name );
		}

		return $this->renderers[ $name ];
	}

	public function resolve_token_factory( string $template_engine ): Token_Factory {
		if ( ! key_exists( $template_engine, $this->engines ) ) {
			$template_engine = Twig\Twig_Template_Engine::NAME;
		}

		if ( ! key_exists( $template_engine, $this->token_factories ) ) {
			$this->token_factories[ $template_engine ] = $this->engines[ $template_engine ]->create_token_factory();
		}

		return $this->token_factories[ $template_engine ];
	}

	public function resolve_integration( string $template_engine ): ?Template_Integration {
		if ( ! key_exists( $template_engine, $this->engines ) ) {
			return null;
		}

		if ( ! key_exists( $template_engine, $this->integrations ) ) {
			$this->integrations[ $template_engine ] = $this->engines[ $template_engine ]->create_integration();
		}

		return $this->integrations[ $template_engine ];
	}

	/**
	 * @return array<string,Template_Integration>
	 */
	public function get_integrations(): array {
		foreach ( array_keys( $this->engines ) as $name ) {
			$this->resolve_integration( $name );
		}

		return $this->integrations;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_choices(): array {
		$choices = array();

		foreach ( $this->engines as $engine ) {
			$choices[ $engine->get_name() ] = $engine->get_label();
		}

		return $choices;
	}

	protected function make_renderer( string $name ): ?Template_Renderer {
		if ( key_exists( $name, $this->engines ) ) {
			$renderer = $this->engines[ $name ]->create_renderer();

			// not every renderer is guaranteed to be available (e.g. Blade requires PHP >= 8.2.0).
			if ( $renderer instanceof File_Template_Renderer_Base &&
			     $renderer->is_available() ) {
				return $renderer;
			}
		}

		return null;
	}
}
