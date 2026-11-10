<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\CSS_Library;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

abstract class CSS_Pattern_Base implements CSS_Library {
	/**
	 * @var array<string,bool>
	 */
	private array $css_handles;

	/**
	 * @param string[] $css_handles all the handles of the library, they are inactive until enabled
	 */
	public function __construct( array $css_handles ) {
		$this->css_handles = array_fill_keys( $css_handles, false );
	}

	/**
	 * Activates the handles of the library.
	 */
	abstract public function enable(): void;

	public function get_css_origin(): string {
		return Asset_Resolver::ORIGIN_LITE;
	}

	public function has_active_css(): bool {
		$css_handles = $this->get_css_handles();
		$inline_css  = $this->get_inline_css();

		return array() !== $css_handles ||
				strlen( $inline_css ) > 0;
	}

	public function get_css_handles(): array {
		$active_handles = array_filter( $this->css_handles );

		return array_keys( $active_handles );
	}

	public function get_css_patches( Asset_Resolver $resolver ): array {
		return array();
	}

	public function get_inline_css(): string {
		return '';
	}

	protected function enable_handle( string $css_handle ): void {
		$this->css_handles[ $css_handle ] = true;
	}
}
