<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Layout_Builder;

final class Layout_Renderer {
	/**
	 * Cache
	 *
	 * @var array<string, string>
	 */
	private array $markups_safe;
	private Layout_Builder $layout_builder;
	private Web_Component_Renderer $renderer;

	public function __construct( Layout_Builder $layout_builder, Web_Component_Renderer $renderer ) {
		$this->layout_builder = $layout_builder;
		$this->renderer       = $renderer;
		$this->markups_safe   = array();
	}

	public function print_markup(
		Layout_Settings $layout_settings,
		int $page_id,
		string $view_markup_safe = '',
		bool $is_skip_cache = false,
		bool $is_ignore_custom_markup = false
	): void {
		$is_passed         = strlen( $view_markup_safe ) > 0;
		$is_custom_ignored = $is_passed || $is_ignore_custom_markup;
		$view_markup_safe  = $is_custom_ignored ?
			$view_markup_safe :
			trim( $layout_settings->custom_markup );
		$is_markup_filled  = strlen( $view_markup_safe ) > 0;
		$view_markup_safe  = $is_markup_filled ?
			$view_markup_safe :
			$this->render_markup( $layout_settings, $is_skip_cache );

		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $view_markup_safe;

		$layout_id                        = $layout_settings->get_unique_id( true );
		$this->markups_safe[ $layout_id ] = $view_markup_safe;
	}

	protected function render_markup( Layout_Settings $layout_settings, bool $is_cache_disabled ): string {
		$short_unique_id = $layout_settings->get_unique_id( true );
		$is_cached       = key_exists( $short_unique_id, $this->markups_safe ) && ! $is_cache_disabled;

		if ( $is_cached ) {
			return $this->markups_safe[ $short_unique_id ];
		}

		return $this->generate_markup( $layout_settings );
	}

	protected function generate_markup( Layout_Settings $layout_settings ): string {
		$root = $this->layout_builder->compose_layout( $layout_settings );

		return $this->renderer->render_component( $root );
	}
}
