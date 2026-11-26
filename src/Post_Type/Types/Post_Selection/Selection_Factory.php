<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Instance_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Rendering\Template_Renderer_Storage;
use WP_REST_Request;

class Selection_Factory extends Instance_Factory {
	protected Post_Query $query_builder;
	protected Selection_Markup $post_selection_markup;
	protected Template_Renderer_Storage $template_renderer_storage;
	protected Html_Renderer $html_renderer;
	private Selection_Settings_Storage $post_selections_settings_storage;

	public function __construct(
		Post_Query $query_builder,
		Selection_Markup $post_selection_markup,
		Template_Renderer_Storage $template_renderer_storage,
		Selection_Settings_Storage $post_selections_settings_storage,
		Html_Renderer $html_renderer
	) {
		$this->query_builder                    = $query_builder;
		$this->post_selection_markup            = $post_selection_markup;
		$this->template_renderer_storage        = $template_renderer_storage;
		$this->post_selections_settings_storage = $post_selections_settings_storage;
		$this->html_renderer                    = $html_renderer;
	}

	public function get_template_fields( Cpt_Theme_Settings $theme_settings ): array {
		return array(
			Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_EXTRA_QUERY_ARGUMENTS ) => $theme_settings->get_controller_template_engine(),
			Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_MARKUP ) => $theme_settings->get_template_engine(),
			Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_CUSTOM_MARKUP ) => $theme_settings->get_template_engine(),
		);
	}

	public function create_selection( Post_Selection_Settings $post_selection_settings, string $classes = '' ): Selection_Instance {
		return new Selection_Instance(
			$this->template_renderer_storage,
			$post_selection_settings,
			$this->query_builder,
			$this->post_selection_markup,
			$this->html_renderer,
			$classes
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_ajax_response( string $unique_id ): array {
		$settings = $this->get_cards_data_storage()->get( $unique_id );
		$card     = $this->create_selection( $settings );

		return $card->get_ajax_response();
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_rest_api_response( string $unique_id, WP_REST_Request $wprest_request ): array {
		$settings       = $this->get_cards_data_storage()->get( $unique_id );
		$post_selection = $this->create_selection( $settings );

		return $post_selection->get_rest_api_response( $wprest_request );
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_template_variables_for_validation( string $unique_id ): array {
		$settings  = $this->post_selections_settings_storage->get( $unique_id );
		$selection = $this->create_selection( $settings );

		return $selection->get_template_variables_for_validation();
	}

	protected function get_cards_data_storage(): Selection_Settings_Storage {
		return $this->post_selections_settings_storage;
	}
}
