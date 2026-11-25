<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode\Shortcode_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Invalid_Source_Args_Exception;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Shortcode_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Source_Args_Normalizer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Layout_Shortcode_Handler extends Shortcode_Base {
	private Layout_Settings_Storage $layouts_settings_storage;
	private Shortcode_Renderer $shortcode_renderer;

	public function __construct(
		Layout_Cpt $public_cpt,
		Settings_Storage $settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Live_Reloader_Component $live_reloader_component,
		Layout_Factory $layout_factory,
		Html_Renderer $html_renderer,
		Shortcode_Renderer $shortcode_renderer
	) {
		parent::__construct( $public_cpt, $settings, $layouts_settings_storage, $layout_factory, $live_reloader_component, $html_renderer );

		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->shortcode_renderer       = $shortcode_renderer;
	}

	public function render_shortcode( array $attrs ): string {
		$layout_id = string( $attrs, 'id' );
		// back compatibility.
		$layout_id = strlen( $layout_id ) > 0 ?
			$layout_id :
			string( $attrs, 'view-id' );

		$unique_id = $this->layouts_settings_storage->get_unique_id_from_shortcode_id( $layout_id, $this->get_post_type() );

		if ( '' === $unique_id ) {
			return $this->get_error_markup(
				$this->get_shortcode_name(),
				$attrs,
				sprintf(
					// translators: %s is a singular post-type name.
					__( '%s is missing', 'acf-views' ),
					$this->public_cpt->labels()->singular_name()
				)
			);
		}

		if ( ! $this->is_layout_available_for_user( $unique_id, $attrs ) ) {
			return '';
		}

		$layout_settings = $this->layouts_settings_storage->get( $unique_id );

		if ( ! $layout_settings->is_multiple() ) {
			$attrs = Source_Args_Normalizer::normalize( $layout_settings->item_type, $attrs );
		}

		try {
			$html = $this->shortcode_renderer->render( $layout_settings, $attrs );
		} catch ( Invalid_Source_Args_Exception $exception ) {
			return $this->get_error_markup( $this->get_shortcode_name(), $attrs, $exception->getMessage() );
		}

		return null !== $html ?
			$this->maybe_add_quick_link_and_shadow_css( $html, $unique_id, $attrs, false ) :
			'';
	}

	protected function get_unique_id_prefix(): string {
		return Layout_Settings::UNIQUE_ID_PREFIX;
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
}
