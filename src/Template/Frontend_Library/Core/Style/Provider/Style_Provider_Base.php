<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

abstract class Style_Provider_Base implements Style_Provider {
	/**
	 * @var string[]
	 */
	private array $active_handles = array();

	public function get_style_origin(): string {
		return Asset_Resolver::ORIGIN_LITE;
	}

	public function has_active_styles(): bool {
		$css_handles = $this->get_style_handles();
		$inline_css  = $this->get_inline_styles();

		return array() !== $css_handles ||
				array() !== $inline_css;
	}

	public function get_style_handles(): array {
		return $this->active_handles;
	}

	public function get_style_patches( Asset_Resolver $resolver ): array {
		return array();
	}

	public function get_inline_styles(): array {
		return array();
	}

	/**
	 * @param string[] $handles
	 */
	protected function enable_handles( array $handles ): void {
		$this->active_handles = $handles;
	}
}
