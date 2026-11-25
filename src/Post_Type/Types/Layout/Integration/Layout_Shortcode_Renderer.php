<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode\Shortcode_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Shortcode_Args;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Shortcode_Args_Parser;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Layout_Printer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Attributes\Source_Args_Normalizer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Layout_Shortcode_Renderer extends Shortcode_Base {
	private Layout_Settings_Storage $layouts_settings_storage;
	private Shortcode_Args_Parser $shortcode_args_parser;
	private Layout_Printer $layout_printer;

	public function __construct(
		Layout_Cpt $public_cpt,
		Settings_Storage $settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Live_Reloader_Component $live_reloader_component,
		Layout_Factory $layout_factory,
		Html_Renderer $html_renderer,
		Shortcode_Args_Parser $shortcode_args_parser,
		Layout_Printer $layout_printer
	) {
		parent::__construct(
			$public_cpt,
			$settings,
			$layouts_settings_storage,
			$layout_factory,
			$live_reloader_component,
			$html_renderer
		);

		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->shortcode_args_parser    = $shortcode_args_parser;
		$this->layout_printer           = $layout_printer;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function render_shortcode( array $attrs ): string {
		$layout_id = self::get_layout_id( $attrs );
		$post_type = $this->get_post_type();
		$unique_id = $this->layouts_settings_storage->get_unique_id_from_shortcode_id( $layout_id, $post_type );

		if ( '' === $unique_id ) {
			return $this->get_missing_layout_markup( $attrs );
		}

		$is_available = $this->is_layout_available_for_user( $unique_id, $attrs );

		if ( $is_available ) {
			return $this->render_layout( $unique_id, $attrs );
		}

		return '';
	}

	protected function get_unique_id_prefix(): string {
		return Layout_Settings::UNIQUE_ID_PREFIX;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function get_missing_layout_markup( array $attrs ): string {
		$singular_name = $this->public_cpt->labels()->singular_name();
		// translators: %s is a singular post-type name.
		$message_template = __( '%s is missing', 'acf-views' );
		$message          = sprintf( $message_template, $singular_name );

		return $this->get_error_markup( $this->get_shortcode_name(), $attrs, $message );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function render_layout( string $unique_id, array $attrs ): string {
		$layout_settings = $this->layouts_settings_storage->get( $unique_id );
		$parsed_attrs    = self::get_normalized_attrs( $layout_settings, $attrs );
		$args            = $this->shortcode_args_parser->parse( $layout_settings, $parsed_attrs );
		$is_parsed       = ! $args->has_error();

		if ( $is_parsed ) {
			return $this->render_parsed_layout( $unique_id, $layout_settings, $args, $parsed_attrs );
		}

		return $this->get_error_markup( $this->get_shortcode_name(), $parsed_attrs, $args->get_error() );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function render_parsed_layout(
		string $unique_id,
		Layout_Settings $layout_settings,
		Shortcode_Args $args,
		array $attrs
	): string {
		$html        = $this->layout_printer->render( $layout_settings, $args );
		$is_rendered = is_string( $html );

		return $is_rendered ?
			$this->maybe_add_quick_link_and_shadow_css( $html, $unique_id, $attrs, false ) :
			'';
	}

	/**
	 * @param mixed[] $attrs
	 */
	protected function is_layout_available_for_user( string $unique_id, array $attrs ): bool {
		$user_roles = wp_get_current_user()->roles;

		$is_shortcode_available = $this->is_shortcode_available_for_user( $user_roles, $attrs );

		$layout_settings = $this->layouts_settings_storage->get( $unique_id );
		$roles_with      = $layout_settings->get_roles_with();
		$roles_without   = $layout_settings->get_roles_without();

		$is_layout_available = self::is_available_for_roles(
			$user_roles,
			$roles_with,
			$roles_without
		);

		return $is_shortcode_available && $is_layout_available;
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

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return array<string,mixed>
	 */
	protected static function get_normalized_attrs( Layout_Settings $layout_settings, array $attrs ): array {
		$is_multiple = $layout_settings->is_multiple();

		return $is_multiple ?
			$attrs :
			Source_Args_Normalizer::normalize( $layout_settings->item_type, $attrs );
	}
}
