<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Postbox_Printer;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_Post;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Selection_Meta_Boxes extends Cpt_Meta_Boxes {
	private Layout_Settings_Storage $layouts_settings_storage;
	private Selection_Settings_Storage $post_selections_settings_storage;
	private Selection_Cpt $public_cpt;
	private Layout_Cpt $layout_cpt;

	public function __construct(
		Postbox_Printer $postbox,
		Plugin $plugin,
		Selection_Settings_Storage $post_selections_settings_storage,
		Layout_Settings_Storage $layouts_settings_storage,
		Selection_Cpt $public_cpt,
		Layout_Cpt $layout_cpt,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $postbox, $plugin, $html_renderer );

		$this->post_selections_settings_storage = $post_selections_settings_storage;
		$this->layouts_settings_storage         = $layouts_settings_storage;
		$this->public_cpt                       = $public_cpt;
		$this->layout_cpt                       = $layout_cpt;
	}

	protected function get_cpt_name(): string {
		return Hard_Post_Selection_Cpt::cpt_name();
	}

	public function print_related_acf_view_meta_box(
		Post_Selection_Settings $post_selection_settings,
		bool $is_skip_not_found_message = false
	): void {
		$nodes = $this->related_acf_view_nodes( $post_selection_settings, $is_skip_not_found_message );

		$this->html_renderer->print_html( ...$nodes );
	}

	/**
	 * @return Html_Node[]
	 */
	public function related_acf_view_nodes(
		Post_Selection_Settings $post_selection_settings,
		bool $is_skip_not_found_message = false
	): array {
		if ( '' === $post_selection_settings->acf_view_id ) {
			$message = sprintf(
				// translators: %s - singular name of the CPT.
				__( 'No related %s.', 'acf-views' ),
				$this->layout_cpt->labels()->singular_name()
			);

			return $is_skip_not_found_message ?
				array() :
				array( $message );
		}

		// here we must use viewsDataStorage, as it's a View.
		$view_data = $this->layouts_settings_storage->get( $post_selection_settings->acf_view_id );

		$edit_link = $view_data->get_edit_post_link();

		return array(
			tag( 'a' )
				->attributes(
					array(
						'href'   => $edit_link,
						'target' => '_blank',
					)
				)
				->content(
					$view_data->title
				),
		);
	}

	public function add_meta_boxes(): void {
		if ( $this->is_templated_post() ) {
			$this->add_template_meta_boxes();
		} else {
			$this->add_query_only_meta_boxes();
		}

		parent::add_meta_boxes();
	}

	protected function is_templated_post(): bool {
		$post = get_post();

		return $post instanceof WP_Post &&
			$this->post_selections_settings_storage->get( $post->post_name )->has_template();
	}

	protected function add_query_only_meta_boxes(): void {
		add_meta_box(
			'acf-cards_displaying_results',
			__( 'Displaying results', 'acf-views' ),
			function (): void {
				echo esc_html( __( 'To display Selection results add it as a Source in any post Layout', 'acf-views' ) );
			},
			array(
				Hard_Post_Selection_Cpt::cpt_name(),
			),
			'side',
			'high'
		);
	}

	protected function add_shortcode_meta_box(): void {
		$print_box = fn( $post ) => $this->print_shortcode_box( $post );

		add_meta_box(
			'acf-cards_shortcode_cpt',
			__( 'Embed the Post Selection', 'acf-views' ),
			$print_box,
			array(
				Hard_Post_Selection_Cpt::cpt_name(),
			),
			'side',
			'high'
		);
	}

	protected function print_shortcode_box( ?WP_Post $post ): void {
		if ( $post instanceof WP_Post &&
			'publish' === $post->post_status ) {
			$selection_settings = $this->post_selections_settings_storage->get( $post->post_name );
			$card_unique_id     = $selection_settings->get_unique_id( true );
			$title              = get_the_title( $post );

			$this->get_postbox()->print_postbox_shortcode(
				$card_unique_id,
				false,
				$this->public_cpt,
				$title,
				true
			);

			return;
		}

		$labels        = $this->public_cpt->labels();
		$singular_name = $labels->singular_name();

		$message = sprintf(
			// translators: %s - singular name of the CPT.
			__( 'Your %s shortcode is available after publishing.', 'acf-views' ),
			$singular_name
		);

		echo esc_html( $message );
	}

	protected function add_template_meta_boxes(): void {
		$this->add_shortcode_meta_box();

		add_meta_box(
			'acf-cards_related_view',
			__( 'Layout for Results', 'acf-views' ),
			function ( WP_Post $wp_post ): void {
				$card_data = $this->post_selections_settings_storage->get( $wp_post->post_name );

				$this->print_related_acf_view_meta_box( $card_data );
			},
			array(
				Hard_Post_Selection_Cpt::cpt_name(),
			),
			'side',
			'core'
		);
	}
}
