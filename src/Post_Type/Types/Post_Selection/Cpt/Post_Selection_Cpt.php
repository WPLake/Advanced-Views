<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

class Post_Selection_Cpt extends Cpt {

	private Selection_Settings_Storage $post_selections_settings_storage;
	private Selection_Renderer $post_selection_renderer;

	public function __construct(
		Selection_Cpt $plugin_cpt,
		Selection_Settings_Storage $post_selections_settings_storage,
		Selection_Renderer $post_selection_renderer,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $plugin_cpt, $post_selections_settings_storage, $html_renderer );

		$this->post_selections_settings_storage = $post_selections_settings_storage;
		$this->post_selection_renderer          = $post_selection_renderer;
	}

	public function add_cpt(): void {
		$labels      = $this->plugin_cpt->labels();
		$plural_name = $labels->plural_name();

		$summary      = __( 'Create a dynamic Post, Product or Custom Post Type query and display its results in any Layout.', 'acf-views' );
		$description  = $this->html_renderer->render_html( $summary );
		$description .= $this->get_storage_label();

		$layout_cpt_name = Hard_Layout_Cpt::cpt_name();
		$menu_slug       = sprintf( 'edit.php?post_type=%s', $layout_cpt_name );

		$cpt_args = array(
			'label'        => $plural_name,
			'description'  => $description,
			'labels'       => $this->get_labels(),
			'show_in_menu' => $menu_slug,
			'menu_icon'    => 'dashicons-layout',
		);

		$this->register_cpt( $cpt_args );
	}

	public function load_more_ajax(): void {
		$response = array(
			'error' => '',
			'html'  => '',
		);

		// string, as unique_id is expected (or int for back compatibility).
		$card_id     = Query_Arguments::get_string_for_non_action( '_acfCardId', 'post' );
		$page_number = Query_Arguments::get_int_for_non_action( '_pageNumber', 'post' );

		$is_request_wrong = '0' === $card_id ||
			0 === $page_number;

		if ( $is_request_wrong ) {
			$response['error'] .= __( 'Request is wrong, required fields are missing.', 'acf-views' );
			echo wp_json_encode( $response );

			exit;
		}

		// can be post id for back compatibility
		// type and post_status will be verified in this call.
		$cards_storage  = $this->get_cards_data_storage();
		$cpt_name       = $this->get_cpt_name();
		$card_unique_id = $cards_storage->get_unique_id_from_shortcode_id( $card_id, $cpt_name );

		if ( '' === $card_unique_id ) {
			$response['error'] .= __( 'Request is wrong, Selection is missing.', 'acf-views' );
			echo wp_json_encode( $response );

			exit;
		}

		$card_data = $cards_storage->get( $card_unique_id );

		if ( ! $card_data->is_with_pagination() ) {
			$response['error'] .= __( 'Request is wrong, Selection is unavailable.', 'acf-views' );
			echo wp_json_encode( $response );

			exit;
		}

		$query_context = Query_Context::new_instance()
			->set_page_number( $page_number );

		$response['html'] = $this->post_selection_renderer->render(
			$card_data,
			$query_context,
			true
		);

		echo wp_json_encode( $response );

		exit;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		parent::set_route_hooks( $route_detector );

		// do not use $isAdmin, because they're ajax hooks and both to the wp-admin, even the 'nopriv' one.
		$cpt_name = $this->get_cpt_name();

		$nopriv_hook = sprintf( 'wp_ajax_nopriv_%s', $cpt_name );
		$hook        = sprintf( 'wp_ajax_%s', $cpt_name );

		self::add_action( $nopriv_hook, array( $this, 'load_more_ajax' ) );
		self::add_action( $hook, array( $this, 'load_more_ajax' ) );
	}

	/**
	 * @param array<string,array<int,string>> $messages
	 *
	 * @return array<string,array<int,string>>
	 */
	public function replace_post_updated_message( array $messages ): array {
		global $post;

		$scheduled_message = self::get_scheduled_message( $post->post_date );
		$restored_message  = self::get_restored_message();
		$cpt_name          = $this->get_cpt_name();

		$messages[ $cpt_name ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Card updated.', 'acf-views' ),
			2  => __( 'Custom field updated.', 'acf-views' ),
			3  => __( 'Custom field deleted.', 'acf-views' ),
			4  => __( 'Card updated.', 'acf-views' ),
			5  => $restored_message,
			6  => __( 'Card published.', 'acf-views' ),
			7  => __( 'Card saved.', 'acf-views' ),
			8  => __( 'Card submitted.', 'acf-views' ),
			9  => $scheduled_message,
			10 => __( 'Card draft updated.', 'acf-views' ),
		);

		return $messages;
	}

	protected function get_cards_data_storage(): Selection_Settings_Storage {
		return $this->post_selections_settings_storage;
	}

	protected static function get_scheduled_message( string $post_date ): string {
		$timestamp = strtotime( $post_date );
		$date      = date_i18n( 'M j, Y @ G:i', $timestamp );
		$label     = __( 'Card scheduled for:', 'acf-views' );

		return sprintf( '%1$s <strong>%2$s</strong>', $label, $date );
	}

	protected static function get_restored_message(): string {
		$revision_id = Query_Arguments::get_int_for_non_action( 'revision' );

		if ( 0 !== $revision_id ) {
			$label    = __( 'Card restored to revision from', 'acf-views' );
			$revision = wp_post_revision_title( $revision_id, false );

			return sprintf( '%1$s %2$s', $label, $revision );
		}

		return '';
	}
}
