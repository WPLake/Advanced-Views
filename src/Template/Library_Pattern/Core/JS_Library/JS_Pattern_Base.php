<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

abstract class JS_Pattern_Base implements JS_Library {
	/**
	 * @var array<string,bool>
	 */
	private array $js_handles;

	/**
	 * @param string[] $js_handles all the handles of the library, they are inactive until enabled
	 */
	public function __construct( array $js_handles ) {
		$this->js_handles = array_fill_keys( $js_handles, false );
	}

	/**
	 * Activates the handles of the library.
	 */
	abstract public function enable(): void;

	public function get_js_origin(): string {
		return Asset_Resolver::ORIGIN_LITE;
	}

	public function has_active_js(): bool {
		$js_handles = $this->get_active_js_handles();

		return array() !== $js_handles;
	}

	public function get_active_js_handles(): array {
		$active_handles = array_filter( $this->js_handles );

		return array_keys( $active_handles );
	}

	public function get_external_scripts(): array {
		return array();
	}

	public function get_js_data(): array {
		return array();
	}

	protected function enable_handle( string $js_handle ): void {
		$this->js_handles[ $js_handle ] = true;
	}
}
