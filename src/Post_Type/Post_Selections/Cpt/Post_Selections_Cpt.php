<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Selection_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Post_Selections_Cpt extends Cpt {

	private Selection_Settings_Storage $post_selections_settings_storage;
	private Post_Selection_Factory $post_selection_factory;

	public function __construct(
		Selections_Cpt $plugin_cpt,
		Selection_Settings_Storage $post_selections_settings_storage,
		Post_Selection_Factory $post_selection_factory,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $plugin_cpt, $post_selections_settings_storage, $html_renderer );

		$this->post_selections_settings_storage = $post_selections_settings_storage;
		$this->post_selection_factory           = $post_selection_factory;
	}

	protected function get_cards_data_storage(): Selection_Settings_Storage {
		return $this->post_selections_settings_storage;
	}

	public function add_cpt(): void {
		$labels        = $this->plugin_cpt->labels();
		$singular_name = $labels->singular_name();
		$plural_name   = $labels->plural_name();

		$tags = array(
			__( 'Query posts, products or custom post types and display each result using a Layout with a', 'acf-views' ) . ' ',
			self::link_tags( 'https://docs.advanced-views.com/getting-started/first-post-selection', $singular_name ),
			'. ',
			tag( 'br' ),
			__( 'Add filters, sorting and pagination as needed.', 'acf-views' ),
		);

		$description  = $this->html_renderer->render_html( ...$tags );
		$description .= $this->get_storage_label();

		$cpt_args = array(
			'label'        => $plural_name,
			'description'  => $description,
			'labels'       => $this->get_labels(),
			'show_in_menu' => sprintf( 'edit.php?post_type=%s', Hard_Layout_Cpt::cpt_name() ),
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

		if ( '0' === $card_id ||
			0 === $page_number ) {
			$response['error'] .= __( 'Request is wrong, required fields are missing.', 'acf-views' );
			echo wp_json_encode( $response );

			exit;
		}

		// can be post id for back compatibility
		// type and post_status will be verified in this call.
		$card_unique_id = $this->get_cards_data_storage()->get_unique_id_from_shortcode_id( $card_id, $this->get_cpt_name() );

		if ( '' === $card_unique_id ) {
			$response['error'] .= __( 'Request is wrong, Selection is missing.', 'acf-views' );
			echo wp_json_encode( $response );

			exit;
		}

		$card_data = $this->get_cards_data_storage()->get( $card_unique_id );

		if ( ! $card_data->is_with_pagination ) {
			$response['error'] .= __( 'Request is wrong, Selection is unavailable.', 'acf-views' );
			echo wp_json_encode( $response );

			exit;
		}

		$query_context = Query_Context::new_instance()
			->set_page_number( $page_number );

		ob_start();
		$this->post_selection_factory->make_and_print_html(
			$card_data,
			$query_context,
			true
		);
		$response['html'] = (string) ob_get_clean();

		echo wp_json_encode( $response );

		exit;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		parent::set_route_hooks( $route_detector );

		// do not use $isAdmin, because they're ajax hooks and both to the wp-admin, even the 'nopriv' one.
		self::add_action( 'wp_ajax_nopriv_' . $this->get_cpt_name(), array( $this, 'load_more_ajax' ) );
		self::add_action( 'wp_ajax_' . $this->get_cpt_name(), array( $this, 'load_more_ajax' ) );
	}

	/**
	 * @param array<string,array<int,string>> $messages
	 *
	 * @return array<string,array<int,string>>
	 */
	public function replace_post_updated_message( array $messages ): array {
		global $post;

		$restored_message   = '';
		$scheduled_message  = __( 'Card scheduled for:', 'acf-views' );
		$scheduled_message .= sprintf(
			' <strong>%1$s</strong>',
			date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) )
		);

		$revision_id = Query_Arguments::get_int_for_non_action( 'revision' );

		if ( 0 !== $revision_id ) {
			$restored_message  = __( 'Card restored to revision from', 'acf-views' );
			$restored_message .= ' ' . wp_post_revision_title( $revision_id, false );
		}

		$messages[ $this->get_cpt_name() ] = array(
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
}
