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
			$this->capture_markup( $layout_settings, $is_skip_cache );

		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $view_markup_safe;

		$layout_id                        = $layout_settings->get_unique_id( true );
		$this->markups_safe[ $layout_id ] = $view_markup_safe;
	}

	protected function capture_markup( Layout_Settings $layout_settings, bool $is_skip_cache ): string {
		ob_start();
		$this->print_safe_markup( $layout_settings, $is_skip_cache );
		$markup_safe = (string) ob_get_clean();

		// remove the empty class attribute if the generation is disabled.
		$is_class_generation_none = Layout_Settings::CLASS_GENERATION_NONE === $layout_settings->classes_generation;

		return $is_class_generation_none ?
			str_replace( ' class=""', '', $markup_safe ) :
			$markup_safe;
	}

	protected function generate_markup( Layout_Settings $layout_settings ): void {
		$root = $this->layout_builder->compose_layout( $layout_settings );
		$html = $this->renderer->render_component( $root );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	protected function print_safe_markup( Layout_Settings $layout_settings, bool $is_cache_disabled ): void {
		$short_unique_id = $layout_settings->get_unique_id( true );
		$is_cached       = key_exists( $short_unique_id, $this->markups_safe ) && ! $is_cache_disabled;

		if ( $is_cached ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->markups_safe[ $short_unique_id ];
		} else {
			$this->generate_markup( $layout_settings );
		}
	}
}
