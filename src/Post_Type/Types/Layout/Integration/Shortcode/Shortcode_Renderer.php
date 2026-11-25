<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;

/**
 * Renders a Layout (a single or a multiple one) for the already parsed shortcode args.
 */
final class Shortcode_Renderer {
	private Layout_Factory $layout_factory;
	/**
	 * Used to avoid recursion with post_object/relationship fields
	 *
	 * @var array<string,bool>
	 */
	private array $displaying_views;

	public function __construct( Layout_Factory $layout_factory ) {
		$this->layout_factory = $layout_factory;

		$this->displaying_views = array();
	}

	/**
	 * @return string|null Null if nothing should be displayed (a recursion).
	 */
	public function render( Layout_Settings $layout_settings, Shortcode_Args $args ): ?string {
		$unique_id = $layout_settings->get_unique_id();

		if ( $layout_settings->is_multiple() ) {
			return $this->render_html( $unique_id, $args );
		}

		$source_id = $args->get_source()->get_id();

		// recursionKey must consist from both. It's allowed to use the same View for a post_object field, but with another id.
		$recursion_key    = sprintf( '%s-%s', $unique_id, $source_id );
		$is_first_display = ! isset( $this->displaying_views[ $recursion_key ] );

		/**
		 * A post_object/relationship recursion (PostA links PostB, PostB links PostA) renders nothing, without an error,
		 * so the user can display PostB inside PostA.
		 */
		if ( $is_first_display ) {
			$this->displaying_views[ $recursion_key ] = true;

			$html = $this->render_html( $unique_id, $args );

			unset( $this->displaying_views[ $recursion_key ] );

			return $html;
		}

		return null;
	}

	protected function render_html( string $unique_id, Shortcode_Args $args ): string {
		ob_start();
		$this->layout_factory->make_and_print_html(
			$args->get_source(),
			$unique_id,
			$args->get_current_page_id(),
			$args->get_classes(),
			$args->get_custom_arguments(),
			$args->get_local_data(),
			$args->get_item_ids()
		);

		return (string) ob_get_clean();
	}
}
