<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Layout_Builder;

class Layout_Renderer {
	/**
	 * Cache
	 *
	 * @var array<string, string>
	 */
	private array $markups_safe;
	private Layout_Builder $layout_builder;
	protected Web_Component_Renderer $renderer;

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
		$view_markup_safe = ( strlen( $view_markup_safe ) > 0 ||
								$is_ignore_custom_markup ) ?
			$view_markup_safe :
			trim( $layout_settings->custom_markup );

		if ( 0 === strlen( $view_markup_safe ) ) {
			ob_start();
			$this->print_safe_markup( $layout_settings, $is_skip_cache );
			$view_markup_safe = (string) ob_get_clean();

			// remove the empty class attribute if the generation is disabled.
			if ( Layout_Settings::CLASS_GENERATION_NONE === $layout_settings->classes_generation ) {
				$view_markup_safe = str_replace( ' class=""', '', $view_markup_safe );
			}

			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $view_markup_safe;
		} else {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $view_markup_safe;
		}

		$layout_id                        = $layout_settings->get_unique_id( true );
		$this->markups_safe[ $layout_id ] = $view_markup_safe;
	}

	protected function generate_markup( Layout_Settings $layout_settings ): void {
		$root = $this->layout_builder->compose_layout( $layout_settings );
		$html = $this->renderer->render_component( $root );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	protected function print_safe_markup( Layout_Settings $layout_settings, bool $is_cache_disabled ): void {
		$short_unique_id = $layout_settings->get_unique_id( true );

		if ( key_exists( $short_unique_id, $this->markups_safe ) &&
			! $is_cache_disabled ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->markups_safe[ $short_unique_id ];
		} else {
			$this->generate_markup( $layout_settings );
		}
	}
}
