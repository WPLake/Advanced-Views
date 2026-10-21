<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Markup;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Tags_Renderer
 * @phpstan-import-type Tag_Group from Tags_Renderer
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
class Import_Printer {
	private Html_Printer $html;

	public function __construct( Html_Printer $html ) {
		$this->html = $html;
	}

	public function print_dashboard_import(
		bool $is_has_demo_objects,
		string $form_nonce,
		bool $is_with_form_message,
		Demo_Importer $demo_import
	): void {
		Tags_Renderer::print_tags(
			$this->html->dashboard_form_tags(
				'import',
				$form_nonce,
				array(
					$is_with_form_message ?
						$this->import_result_block_tags( $is_has_demo_objects, $demo_import ) :
						array(),
					$is_has_demo_objects ?
						array() :
						$this->import_introduction_tags(),
				)
			)
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function import_result_block_tags( bool $is_has_demo_objects, Demo_Importer $demo_import ): array {
		$delete_button = $is_has_demo_objects ?
			array(
				$this->html->tag( 'br' ),
				$this->html->tag( 'br' ),
				array(
					$this->html->tag(
						'button',
						array(
							'class' => 'button button-primary button-large av-dashboard__button av-dashboard__button--red',
							'name'  => '_delete',
							'value' => 'delete',
						)
					),
					$this->html->escaped_text( __( 'Delete imported objects', 'acf-views' ) ),
				),
			) :
			array();

		return array(
			$this->html->tag( 'div', array( 'class' => 'av-introduction av-dashboard__block av-dashboard__block--medium' ) ),
			array_merge( $this->import_result_tags( $demo_import ), $delete_button ),
		);
	}

	/**
	 * @return Tag_Group
	 */
	protected function import_result_tags( Demo_Importer $demo_import ): array {
		$status = $demo_import->is_processed() ?
			$this->import_status_tags( $demo_import ) :
			array();
		$items  = $demo_import->is_has_data() && ! $demo_import->is_has_error() ?
			$this->imported_items_tags( $demo_import ) :
			array();

		return array_merge( $status, $items );
	}

	/**
	 * @return Tag_Group
	 */
	protected function import_status_tags( Demo_Importer $demo_import ): array {
		if ( ! $demo_import->is_has_error() ) {
			$message = $demo_import->is_import_request() ?
				__( 'Import was successful. You’re all set!', 'acf-views' ) :
				__( 'All demo objects have been deleted.', 'acf-views' );

			return array( $this->html->title_tags( $message ) );
		}

		return array(
			$this->html->title_tags( __( 'Request is failed.', 'acf-views' ) ),
			$this->html->tag( 'br' ),
			$this->html->tag( 'br' ),
			$this->html->escaped_text( $demo_import->get_error() ),
		);
	}

	/**
	 * @return Tag_Group
	 */
	protected function imported_items_tags( Demo_Importer $demo_import ): array {
		$same_page_title = __( "Display page's ACF fields on the same page", 'acf-views' );
		$single_title    = __( 'Display a specific post, page or CPT item with its fields', 'acf-views' );
		$filters_title   = array(
			__( 'Display specific posts, pages or CPT items and their fields by using filters', 'acf-views' ),
			__( 'or by manually assigning items', 'acf-views' ),
		);

		return array_merge(
			array(
				$this->html->title_tags( __( 'Imported items', 'acf-views' ) ),
				$this->bold_paragraph_tags( array( $same_page_title ) ),
			),
			$this->spaced_link_tags( $demo_import->get_samsung_link(), __( '"Samsung Galaxy A53" Page', 'acf-views' ) ),
			$this->spaced_link_tags( $demo_import->get_nokia_link(), __( '"Nokia X20" Page', 'acf-views' ) ),
			$this->spaced_link_tags( $demo_import->get_xiaomi_link(), __( '"Xiaomi 12T" Page', 'acf-views' ) ),
			$this->spaced_link_tags( $demo_import->get_acf_group_link(), __( '"Phone" Field Group', 'acf-views' ) ),
			$this->spaced_link_tags( $demo_import->get_phone_acf_view_link(), __( '"Phone" View', 'acf-views' ) ),
			array( $this->bold_paragraph_tags( array( $single_title ) ) ),
			$this->spaced_link_tags( $demo_import->get_samsung_article_link(), __( '"Article about Samsung" page', 'acf-views' ) ),
			array( $this->bold_paragraph_tags( $filters_title ) ),
			$this->spaced_link_tags( $demo_import->get_phones_acf_card_link(), __( '"Phones" Card', 'acf-views' ) ),
			$this->spaced_link_tags( $demo_import->get_phones_article_link(), __( '"Most popular phones in 2022" page', 'acf-views' ) )
		);
	}

	/**
	 * @param string[] $lines
	 *
	 * @return Tag_Chain
	 */
	protected function bold_paragraph_tags( array $lines ): array {
		$line_tags = array();

		foreach ( $lines as $index => $line ) {
			if ( $index > 0 ) {
				$line_tags[] = $this->html->tag( 'br' );
			}

			$line_tags[] = $this->html->escaped_text( $line );
		}

		return array( $this->html->tag( 'p' ), $this->html->tag( 'b' ), $line_tags );
	}

	/**
	 * @return Tag_Group
	 */
	protected function spaced_link_tags( string $url, string $label ): array {
		return array(
			$this->html->link_tags( $url, $label ),
			$this->html->tag( 'br' ),
			$this->html->tag( 'br' ),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function import_introduction_tags(): array {
		$description = array(
			$this->html->tag( 'p', array( 'class' => 'av-introduction__description' ) ),
			array(
				$this->html->escaped_text(
					__(
						"Whether you're new to Advanced Views or just want a quick way to set up the basics, this demo import will automatically create a working example for you.",
						'acf-views'
					)
				),
				$this->html->tag( 'br' ),
				$this->html->escaped_text( __( "It's perfect for exploring how Layouts, Post Selections, and ACF fields work together.", 'acf-views' ) ),
			),
		);

		$importing_description = array(
			$this->html->tag( 'p', array( 'class' => 'av-introduction__description' ) ),
			$this->html->escaped_text(
				__( 'Click Import Demo and wait a few seconds.', 'acf-views' ) . ' ' .
				__( "When done, you'll see quick links to edit all imported items.", 'acf-views' )
			),
		);

		return array(
			$this->html->tag( 'div', array( 'class' => 'av-introduction av-dashboard__block' ) ),
			array(
				$this->html->title_tags( __( 'Import Demo to Get Started in Seconds', 'acf-views' ) ),
				$description,
				$this->html->tag( 'hr' ),
				$this->bold_paragraph_tags( array( __( 'What this demo does', 'acf-views' ) ) ),
				$this->bold_paragraph_tags( array( __( "Display page's ACF fields on the same page", 'acf-views' ) ) ),
				$this->ordered_list_tags(
					array(
						__( "Creates 'draft' pages for 'Samsung Galaxy A53', 'Nokia X20' and 'Xiaomi 12T'.", 'acf-views' ),
						__( 'Creates an ACF Field Group called "Phone" assigned to those pages.', 'acf-views' ),
						__( 'Creates a Layout called "Phone" that displays fields from the "Phone" Field Group.', 'acf-views' ),
						__( 'Populates each page’s ACF fields with sample text and adds the Layout shortcode to the page content.', 'acf-views' ),
					)
				),
				$this->bold_paragraph_tags( array( __( 'Display a specific post, page or CPT item with its fields', 'acf-views' ) ) ),
				$this->ordered_list_tags(
					array(
						__( 'Creates a "draft" page called "Article about Samsung"', 'acf-views' ),
						__( 'Adds a Layout shortcode to the page content with "object-id" parameter pointing to "Samsung Galaxy A53".', 'acf-views' ),
					)
				),
				$this->bold_paragraph_tags( array( __( 'Display multiple items using Post Selections', 'acf-views' ) ) ),
				$this->ordered_list_tags(
					array(
						__( 'Creates a Post Selection called "List of Phones" using the "Phone" Layout and filtering for the phone pages.', 'acf-views' ),
						__( "Create a 'draft' page called 'Most popular phones in 2022' and adds the Post Selection shortcode to the page content.", 'acf-views' ),
					)
				),
				$this->html->tag( 'hr' ),
				$this->bold_paragraph_tags( array( __( 'Importing', 'acf-views' ) ) ),
				$importing_description,
				$this->bold_paragraph_tags( array( __( 'After Import', 'acf-views' ) ) ),
				array(
					$this->html->tag( 'p' ),
					$this->html->escaped_text( __( 'A Delete Demo button will appear if you’d like to remove the demo content later.', 'acf-views' ) ),
				),
				$this->html->tag( 'hr' ),
				$this->html->tag( 'br' ),
				$this->html->tag( 'br' ),
				array(
					$this->html->tag(
						'button',
						array(
							'class' => 'button button-primary button-large',
							'name'  => '_import',
							'value' => 'import',
						)
					),
					$this->html->escaped_text( __( 'Import Demo Now', 'acf-views' ) ),
				),
			),
		);
	}

	/**
	 * @param string[] $items
	 *
	 * @return Tag_Chain
	 */
	protected function ordered_list_tags( array $items ): array {
		return array(
			$this->html->tag( 'ol', array( 'class' => 'av-introduction__description av-introduction__ol' ) ),
			array_map(
				fn( string $item ) => array( $this->html->tag( 'li' ), $this->html->escaped_text( $item ) ),
				$items
			),
		);
	}
}
