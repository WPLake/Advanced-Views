<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Integration;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode\Shortcode_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use WP_Post;

defined( 'ABSPATH' ) || exit;

final class Post_Selection_Shortcode extends Shortcode_Base {
	protected Selection_Renderer $selection_renderer;
	protected Selection_Settings_Storage $cards_data_storage;

	public function __construct(
		Selection_Cpt $public_cpt,
		Settings_Storage $settings,
		Selection_Settings_Storage $post_selections_settings_storage,
		Live_Reloader_Component $live_reloader_component,
		Selection_Renderer $post_selection_renderer,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $public_cpt, $settings, $post_selections_settings_storage, $live_reloader_component, $html_renderer );

		$this->cards_data_storage = $post_selections_settings_storage;
		$this->selection_renderer = $post_selection_renderer;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function render_shortcode( array $attrs ): string {
		$current_user = wp_get_current_user();
		$is_available = $this->is_shortcode_available_for_user( $current_user->roles, $attrs );

		if ( $is_available ) {
			return $this->render_selection( $attrs );
		}

		return '';
	}

	protected static function get_current_post_id(): int {
		global $post;

		$is_loop_post = $post instanceof WP_Post &&
			$post->ID > 0;

		// a) object-id in the current loop
		// b) the current page id.
		return $is_loop_post ?
			$post->ID :
			get_queried_object_id();
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function render_selection( array $attrs ): string {
		$selection_id   = self::get_selection_id( $attrs );
		$post_type      = $this->get_post_type();
		$card_unique_id = $this->cards_data_storage->get_unique_id_from_shortcode_id( $selection_id, $post_type );
		$is_found       = strlen( $card_unique_id ) > 0;

		if ( $is_found ) {
			return $this->render_found_selection( $card_unique_id, $attrs );
		}

		return $this->get_missing_selection_error( $attrs );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function render_found_selection( string $card_unique_id, array $attrs ): string {
		$card_data = $this->cards_data_storage->get( $card_unique_id );

		if ( $card_data->has_template() ) {
			return $this->render_templated_selection( $card_data, $card_unique_id, $attrs );
		}

		return $this->get_no_template_error( $card_data, $attrs );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function render_templated_selection( Post_Selection_Settings $card_data, string $card_unique_id, array $attrs ): string {
		$classes          = self::get_classes( $attrs );
		$custom_arguments = self::get_custom_arguments( $attrs );
		$current_post_id  = self::get_current_post_id();

		$query_context = Query_Context::new_instance()
			->set_post_id( $current_post_id )
			->set_custom_arguments( $custom_arguments );

		$live_reloader = $this->get_live_reloader_component();

		$live_reloader->set_parent_card_id( $card_unique_id );

		$html = $this->selection_renderer->render(
			$card_data,
			$query_context,
			false,
			$classes
		);

		$live_reloader->set_parent_card_id( '' );

		return $this->maybe_add_quick_link_and_shadow_css( $html, $card_unique_id, $attrs, false );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function get_missing_selection_error( array $attrs ): string {
		$labels        = $this->public_cpt->labels();
		$singular_name = $labels->singular_name();
		$shortcode     = $this->get_shortcode_name();

		// translators: %s is a singular post-type name.
		$template = __( '%s is missing', 'acf-views' );
		$message  = sprintf( $template, $singular_name );

		return $this->get_error_markup( $shortcode, $attrs, $message );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_selection_id( array $attrs ): string {
		$selection_id = string( $attrs, 'id' );
		$is_filled    = strlen( $selection_id ) > 0;

		// back compatibility.
		return $is_filled ?
			$selection_id :
			string( $attrs, 'card-id' );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_classes( array $attrs ): string {
		$classes = $attrs['class'] ?? '';

		return is_string( $classes ) ?
			$classes :
			'';
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return array<string,mixed>
	 */
	protected static function get_custom_arguments( array $attrs ): array {
		$custom_arguments = $attrs['custom-arguments'] ?? '';

		// can be an array, if called from Bridge.
		if ( is_string( $custom_arguments ) ) {
			/**
			 * @var array<string,mixed> $parsed_arguments
			 */
			$parsed_arguments = wp_parse_args( $custom_arguments );

			return $parsed_arguments;
		}

		if ( is_array( $custom_arguments ) ) {
			/**
			 * @var array<string,mixed> $custom_arguments
			 */
			return $custom_arguments;
		}

		return array();
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function get_no_template_error( Post_Selection_Settings $card_data, array $attrs ): string {
		$shortcode = $this->get_shortcode_name();
		$unique_id = $card_data->get_unique_id( true );

		// translators: %s is a Post Selection unique id.
		$template = __( 'Post Selection (#%s) has no Template to render', 'acf-views' );
		$message  = sprintf( $template, $unique_id );

		return $this->get_error_markup( $shortcode, $attrs, $message );
	}
}
