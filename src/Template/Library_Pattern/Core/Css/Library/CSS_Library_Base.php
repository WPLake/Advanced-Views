<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Css\Library;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

abstract class CSS_Library_Base implements CSS_Library {
	/**
	 * @var string[]
	 */
	private array $active_handles = array();

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
		return $this->active_handles;
	}

	public function get_css_patches( Asset_Resolver $resolver ): array {
		return array();
	}

	public function get_inline_css(): string {
		return '';
	}

	/**
	 * @param string[] $handles
	 */
	protected function enable_handles( array $handles ): void {
		$this->active_handles = $handles;
	}
}
