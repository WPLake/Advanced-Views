<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

abstract class Script_Provider_Base implements Script_Provider {
	/**
	 * @var string[]
	 */
	private array $active_handles = array();

	public function get_script_origin(): string {
		return Asset_Resolver::ORIGIN_LITE;
	}

	public function has_active_scripts(): bool {
		$handles = $this->get_active_js_handles();

		return count( $handles ) > 0;
	}

	public function get_active_js_handles(): array {
		return $this->active_handles;
	}

	public function get_external_scripts(): array {
		return array();
	}

	public function get_script_variables(): array {
		return array();
	}

	/**
	 * @param string[] $handles
	 */
	protected function enable_handles( array $handles ): void {
		$this->active_handles = $handles;
	}
}
