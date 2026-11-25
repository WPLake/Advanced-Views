<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout;

defined( 'ABSPATH' ) || exit;

use DateTime;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Core\Instance;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Instance\Layout_Request_Responder;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Instance\Layout_Template_Printer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Instance\Layout_Variables_Builder;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_REST_Request;


/**
 * A Layout prepared for a single render or response: it holds the render state and delegates the jobs.
 */
class Layout_Instance extends Instance {
	private Layout_Settings $layout_settings;
	private Field_Provider_Cluster $provider_cluster;
	private Layout_Variables_Builder $variables_builder;
	private Layout_Template_Printer $template_printer;
	private Layout_Request_Responder $request_responder;
	private Source $source;
	/**
	 * Used e.g. for inner Views or Pods Blocks
	 *
	 * @var array<string|int,mixed>|null
	 */
	private ?array $local_data;
	/**
	 * @var int[]
	 */
	private array $item_ids;

	public function __construct(
		Field_Provider_Cluster $provider_cluster,
		Layout_Variables_Builder $variables_builder,
		Layout_Template_Printer $template_printer,
		Layout_Request_Responder $request_responder,
		string $twig_template,
		Layout_Settings $layout_settings,
		Source $source,
		Html_Renderer $html_renderer,
		string $classes = ''
	) {
		parent::__construct( $layout_settings, $twig_template, $html_renderer, $classes );

		$this->layout_settings   = $layout_settings;
		$this->provider_cluster  = $provider_cluster;
		$this->variables_builder = $variables_builder;
		$this->template_printer  = $template_printer;
		$this->request_responder = $request_responder;
		$this->source            = $source;
		$this->local_data        = null;
		$this->item_ids          = array();
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_ajax_response( string $php_code = '' ): array {
		$php_variables = $this->get_view_data()->php_variables;

		return parent::get_ajax_response( $php_variables );
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_rest_api_response( WP_REST_Request $wprest_request, string $php_code = '' ): array {
		$php_variables = $this->get_view_data()->php_variables;

		return parent::get_rest_api_response( $wprest_request, $php_variables );
	}

	/**
	 * @return mixed
	 */
	public function get_field_value(
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		?Item_Settings $item_settings = null,
		bool $is_formatted = false
	) {
		return $this->provider_cluster->get_field_value(
			$field_settings,
			$field_meta,
			$this->source,
			$item_settings,
			$is_formatted,
			$this->local_data
		);
	}

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime {
		return $this->provider_cluster->convert_string_to_date_time( $field_meta, $value );
	}

	public function get_source(): Source {
		return $this->source;
	}

	public function with_source( Source $source ): self {
		$instance         = clone $this;
		$instance->source = $source;

		return $instance;
	}

	/**
	 * @param array<string,mixed> $custom_arguments
	 */
	public function insert_fields_and_print_html(
		array $custom_arguments = array()
	): bool {
		$template = $this->get_template();

		$twig_variables = $this->get_template_variables( false, $custom_arguments );

		ob_start();
		$is_rendered = $this->render_template_and_print_html( $template, $twig_variables );
		$html        = (string) ob_get_clean();

		// shortcode support (necessary for the relationship field with the Field Layout option and others).
		echo do_shortcode( $html );

		return $is_rendered;
	}

	public function get_view_data(): Layout_Settings {
		return $this->layout_settings;
	}

	/**
	 * @param array<string|int,mixed>|null $local_data
	 */
	public function set_local_data( ?array $local_data ): void {
		$this->local_data = $local_data;
	}

	/**
	 * @param int[] $item_ids Used only by the Multiple format
	 */
	public function set_item_ids( array $item_ids ): void {
		$this->item_ids = $item_ids;
	}

	/**
	 * @return int[]
	 */
	public function get_item_ids(): array {
		return $this->item_ids;
	}

	/**
	 * @param array<string,mixed> $variables
	 */
	protected function render_template_and_print_html(
		string $template,
		array $variables,
		bool $is_for_validation = false
	): bool {
		$items_count = count( $this->item_ids );

		// the validation renders fake data, it must never be skipped.
		$is_empty_render = ! $is_for_validation && $this->template_printer->is_empty_render(
			$this->layout_settings,
			$variables,
			$items_count
		);

		// do not render, as Twig saves template in cache
		// so if it's first, then it'll use the empty one for all next calls of this view.
		if ( $is_empty_render ) {
			return false;
		}

		$is_printed = $this->template_printer->print( $this->layout_settings, $template, $variables, $is_for_validation );

		if ( ! $is_printed ) {
			$this->print_template_engine_is_not_loaded_message();
		}

		return true;
	}

	/**
	 * @param array<string,mixed> $custom_arguments
	 *
	 * @return array<string,mixed>
	 */
	protected function get_template_variables(
		bool $is_for_validation = false,
		array $custom_arguments = array()
	): array {
		$classes   = $this->get_classes();
		$container = $this->get_container();

		return $is_for_validation ?
			$this->variables_builder->build_for_validation( $this, $classes, $container ) :
			$this->variables_builder->build( $this, $classes, $custom_arguments, $container );
	}

	/**
	 * @param mixed $controller
	 *
	 * @return array<string,mixed>
	 */
	protected function get_ajax_response_args( $controller ): array {
		$container = $this->get_container();

		return $this->request_responder->get_ajax_response( $controller, $container );
	}

	/**
	 * @param mixed $controller
	 *
	 * @return array<string,mixed>
	 */
	protected function get_rest_api_response_args( WP_REST_Request $wprest_request, $controller ): array {
		$container = $this->get_container();

		return $this->request_responder->get_rest_api_response( $wprest_request, $controller, $container );
	}
}
