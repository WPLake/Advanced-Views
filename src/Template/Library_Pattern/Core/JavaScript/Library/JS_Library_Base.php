<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Library;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

abstract class JS_Library_Base implements JS_Library {
	/**
	 * @var string[]
	 */
	private array $active_handles = array();

	public function get_js_origin(): string {
		return Asset_Resolver::ORIGIN_LITE;
	}

	public function has_active_js(): bool {
		$js_handles = $this->get_active_js_handles();

		return count( $js_handles ) > 0;
	}

	public function get_active_js_handles(): array {
		return $this->active_handles;
	}

	public function get_external_scripts(): array {
		return array();
	}

	public function get_js_data(): array {
		return array();
	}

	/**
	 * @param string[] $handles
	 */
	protected function enable_handles( array $handles ): void {
		$this->active_handles = $handles;
	}
}
