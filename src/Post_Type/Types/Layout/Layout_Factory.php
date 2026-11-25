<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Asset_Collector;
use Org\Wplake\Advanced_Views\Post_Type\Core\Instance_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Library\Library_Asset_Collector;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Markup_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Variable_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Template_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_REST_Request;

class Layout_Factory extends Instance_Factory {
	private Layout_Settings_Storage $layouts_settings_storage;
	private Markup_Provider $markup_provider;
	protected Field_Provider_Cluster $provider_cluster;
	protected Html_Renderer $html_renderer;
	protected Variable_Builder $variables_builder;
	protected Template_Renderer $template_renderer;

	public function __construct(
		Cpt_Asset_Collector $assets_collector,
		Library_Asset_Collector $library_asset_collector,
		Layout_Settings_Storage $layouts_settings_storage,
		Markup_Provider $markup_provider,
		Field_Provider_Cluster $provider_cluster,
		Html_Renderer $html_renderer,
		Variable_Builder $variables_builder,
		Template_Renderer $template_renderer
	) {
		parent::__construct( $assets_collector, $library_asset_collector );

		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->markup_provider          = $markup_provider;
		$this->provider_cluster         = $provider_cluster;
		$this->html_renderer            = $html_renderer;
		$this->variables_builder        = $variables_builder;
		$this->template_renderer        = $template_renderer;
	}

	public function get_template_fields( Cpt_Theme_Settings $theme_settings ): array {
		return array(
			Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_PHP_VARIABLES ) => $theme_settings->get_controller_template_engine(),
			Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_MARKUP ) => $theme_settings->get_template_engine(),
			Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_CUSTOM_MARKUP ) => $theme_settings->get_template_engine(),
		);
	}

	public function make(
		Source $source,
		string $unique_view_id,
		int $page_id,
		?Layout_Settings $layout_settings = null,
		string $classes = ''
	): Layout_Instance {
		$layout_settings ??= $this->layouts_settings_storage->get( $unique_view_id );

		ob_start();
		$this->markup_provider->print_markup( $layout_settings, $page_id );
		$markup = (string) ob_get_clean();

		return $this->create_layout_instance( $source, $layout_settings, $markup, $classes );
	}

	/**
	 * @param mixed[] $custom_arguments
	 * @param mixed[]|null $local_data
	 * @param int[] $item_ids Used only by the Multiple format
	 */
	public function make_and_print_html(
		Source $source,
		string $view_unique_id,
		int $page_id,
		string $classes = '',
		array $custom_arguments = array(),
		?array $local_data = null,
		array $item_ids = array()
	): void {
		$view = $this->make(
			$source,
			$view_unique_id,
			$page_id,
			null,
			$classes
		);

		$view->set_local_data( $local_data );
		$view->set_item_ids( $item_ids );

		$is_not_empty = $view->insert_fields_and_print_html( $custom_arguments );

		// mark as rendered, only if is not empty
		// 'makeAndGetHtml' used as the primary. 'make' used for the specific cases, like validationInstance.
		if ( $is_not_empty ) {
			$layout_settings = $view->get_view_data();

			$this->assets_collector->add_asset( $layout_settings );
			$this->library_asset_collector->activate_libraries( $layout_settings );
		}
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_ajax_response( string $unique_id ): array {
		$layout = $this->create_flat_layout_instance( $unique_id );

		return $layout->get_ajax_response();
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_rest_api_response( string $unique_id, WP_REST_Request $wprest_request ): array {
		$layout = $this->create_flat_layout_instance( $unique_id );

		return $layout->get_rest_api_response( $wprest_request );
	}

	protected function create_flat_layout_instance( string $unique_id ): Layout_Instance {
		$settings = $this->layouts_settings_storage->get( $unique_id );

		$source = new Source();

		return $this->create_layout_instance(
			$source,
			$settings,
			'',
			''
		);
	}

	protected function create_layout_instance(
		Source $source,
		Layout_Settings $settings,
		string $markup,
		string $classes
	): Layout_Instance {
		return new Layout_Instance(
			$this->provider_cluster,
			$this->variables_builder,
			$this->template_renderer,
			$markup,
			$settings,
			$source,
			$this->html_renderer,
			$classes
		);
	}

	protected function get_template_variables_for_validation( string $unique_id ): array {
		$source = new Source();
		$layout = $this->make( $source, $unique_id, 0 );

		return $layout->get_template_variables_for_validation();
	}
}
