<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode\Shortcode_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Argument_Parser;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Shortcode_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Layout_Shortcode_Renderer extends Shortcode_Base {
	private Layout_Settings_Storage $layouts_settings_storage;
	private Argument_Parser $shortcode_arguments_parser;
	private Layout_Renderer $layout_renderer;

	public function __construct(
		Layout_Cpt $public_cpt,
		Settings_Storage $settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Live_Reloader_Component $live_reloader_component,
		Html_Renderer $html_renderer,
		Argument_Parser $shortcode_arguments_parser,
		Layout_Renderer $layout_renderer
	) {
		parent::__construct(
			$public_cpt,
			$settings,
			$layouts_settings_storage,
			$live_reloader_component,
			$html_renderer
		);

		$this->layouts_settings_storage   = $layouts_settings_storage;
		$this->shortcode_arguments_parser = $shortcode_arguments_parser;
		$this->layout_renderer            = $layout_renderer;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function render_shortcode( array $attrs ): string {
		$unique_id = $this->get_layout_unique_id( $attrs );

		$is_found     = strlen( $unique_id ) > 0;
		$is_available = $is_found &&
						$this->is_layout_accessible( $unique_id, $attrs );

		if ( $is_available ) {
			return $this->render_layout( $unique_id, $attrs );
		}

		return $this->get_missing_layout_markup( $attrs );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function get_layout_unique_id( array $attrs ): string {
		$layout_id = self::get_layout_id( $attrs );
		$post_type = $this->get_post_type();

		return $this->layouts_settings_storage->get_unique_id_from_shortcode_id( $layout_id, $post_type );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function get_missing_layout_markup( array $attrs ): string {
		$message        = $this->get_missing_layout_message();
		$shortcode_name = $this->get_shortcode_name();

		return $this->get_error_markup(
			$shortcode_name,
			$attrs,
			$message
		);
	}

	protected function get_missing_layout_message(): string {
		$singular_name = $this->public_cpt
			->labels()
			->singular_name();
		// translators: %s is a singular post-type name.
		$message_template = __( '%s is missing', 'acf-views' );

		return sprintf( $message_template, $singular_name );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function render_layout( string $unique_id, array $attrs ): string {
		$layout_settings = $this->layouts_settings_storage->get( $unique_id );
		$arguments       = $this->shortcode_arguments_parser->parse_arguments( $layout_settings, $attrs );
		$is_parsed       = ! $arguments->has_error();

		if ( $is_parsed ) {
			return $this->render_parsed_layout(
				$unique_id,
				$layout_settings,
				$arguments,
				$attrs
			);
		}

		$shortcode_name = $this->get_shortcode_name();

		return $this->get_error_markup(
			$shortcode_name,
			$attrs,
			$arguments->error
		);
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function render_parsed_layout(
		string $unique_id,
		Layout_Settings $layout_settings,
		Shortcode_Arguments $arguments,
		array $attrs
	): string {
		$html        = $this->layout_renderer->render(
			$layout_settings,
			$arguments->source,
			$arguments->queried_object_id,
			$arguments->classes,
			$arguments->custom_arguments,
			$arguments->local_data,
			$arguments->item_ids
		);
		$is_rendered = is_string( $html );

		return $is_rendered ?
			$this->maybe_add_quick_link_and_shadow_css(
				$html,
				$unique_id,
				$attrs,
				false
			) :
			'';
	}

	/**
	 * @param mixed[] $attrs
	 */
	protected function is_layout_accessible( string $unique_id, array $attrs ): bool {
		$user_roles             = wp_get_current_user()->roles;
		$is_shortcode_available = $this->is_shortcode_accessible( $user_roles, $attrs );
		$is_layout_available    = $this->is_layout_allowed_for_roles( $unique_id, $user_roles );

		return $is_shortcode_available && $is_layout_available;
	}

	/**
	 * @param string[] $user_roles
	 */
	protected function is_layout_allowed_for_roles( string $unique_id, array $user_roles ): bool {
		$layout_settings = $this->layouts_settings_storage->get( $unique_id );
		$roles_with      = $layout_settings->get_roles_with();
		$roles_without   = $layout_settings->get_roles_without();

		return self::is_available_for_roles(
			$user_roles,
			$roles_with,
			$roles_without
		);
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_layout_id( array $attrs ): string {
		$layout_id = string( $attrs, 'id' );
		$is_filled = strlen( $layout_id ) > 0;

		// back compatibility.
		return $is_filled ?
			$layout_id :
			string( $attrs, 'view-id' );
	}
}
