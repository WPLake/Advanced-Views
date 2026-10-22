<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Html
 * @phpstan-import-type Tag_Group from Html
 * @phpstan-import-type Tag_Tree from Html
 */
final class Import_Printer {
	public static function print_dashboard_import(
		bool $is_has_demo_objects,
		string $form_nonce,
		bool $is_with_form_message,
		Demo_Importer $demo_import
	): void {
		$result_block = $is_with_form_message ?
			self::import_result_block_tags( $is_has_demo_objects, $demo_import ) :
			array();
		$introduction = $is_has_demo_objects ?
			array() :
			self::import_introduction_tags();
		$blocks       = array(
			$result_block,
			$introduction,
		);
		$form_tags    = Html_Printer::dashboard_form_tags( 'import', $form_nonce, $blocks );

		Html_Renderer::print_tags( $form_tags );
	}

	/**
	 * @return Tag_Tree
	 */
	protected static function import_result_block_tags( bool $is_has_demo_objects, Demo_Importer $demo_import ): array {
		$button_attrs  = array(
			'class' => 'button button-primary button-large av-dashboard__button av-dashboard__button--red',
			'name'  => '_delete',
			'value' => 'delete',
		);
		$button_label  = __( 'Delete imported objects', 'acf-views' );
		$button_tag    = array(
			'tag'   => 'button',
			'attrs' => $button_attrs,
		);
		$delete_button = $is_has_demo_objects ?
			array(
				array( 'tag' => 'br' ),
				array( 'tag' => 'br' ),
				array( $button_tag, $button_label ),
			) :
			array();

		$block_attrs   = array( 'class' => 'av-introduction av-dashboard__block av-dashboard__block--medium' );
		$result_tags   = self::import_result_tags( $demo_import );
		$block_members = array_merge( $result_tags, $delete_button );

		return array(
			array(
				'tag'   => 'div',
				'attrs' => $block_attrs,
			),
			$block_members,
		);
	}

	/**
	 * @return Tag_Group
	 */
	protected static function import_result_tags( Demo_Importer $demo_import ): array {
		$is_has_items = $demo_import->is_has_data() && ! $demo_import->is_has_error();
		$status       = $demo_import->is_processed() ?
			self::import_status_tags( $demo_import ) :
			array();
		$items        = $is_has_items ?
			self::imported_items_tags( $demo_import ) :
			array();

		return array_merge( $status, $items );
	}

	/**
	 * @return Tag_Group
	 */
	protected static function import_status_tags( Demo_Importer $demo_import ): array {
		if ( ! $demo_import->is_has_error() ) {
			$message = $demo_import->is_import_request() ?
				__( 'Import was successful. You’re all set!', 'acf-views' ) :
				__( 'All demo objects have been deleted.', 'acf-views' );

			return array( Html_Printer::title_tags( $message ) );
		}

		$failed_title = __( 'Request is failed.', 'acf-views' );
		$error        = $demo_import->get_error();

		return array(
			Html_Printer::title_tags( $failed_title ),
			array( 'tag' => 'br' ),
			array( 'tag' => 'br' ),
			$error,
		);
	}

	/**
	 * @return Tag_Group
	 */
	protected static function imported_items_tags( Demo_Importer $demo_import ): array {
		$same_page_title = __( "Display page's ACF fields on the same page", 'acf-views' );
		$single_title    = __( 'Display a specific post, page or CPT item with its fields', 'acf-views' );
		$filters_title   = array(
			__( 'Display specific posts, pages or CPT items and their fields by using filters', 'acf-views' ),
			__( 'or by manually assigning items', 'acf-views' ),
		);
		$imported_title  = __( 'Imported items', 'acf-views' );

		$samsung_label       = __( '"Samsung Galaxy A53" Page', 'acf-views' );
		$nokia_label         = __( '"Nokia X20" Page', 'acf-views' );
		$xiaomi_label        = __( '"Xiaomi 12T" Page', 'acf-views' );
		$group_label         = __( '"Phone" Field Group', 'acf-views' );
		$view_label          = __( '"Phone" View', 'acf-views' );
		$samsung_article     = __( '"Article about Samsung" page', 'acf-views' );
		$phones_card_label   = __( '"Phones" Card', 'acf-views' );
		$phones_article_name = __( '"Most popular phones in 2022" page', 'acf-views' );

		$samsung_link         = $demo_import->get_samsung_link();
		$nokia_link           = $demo_import->get_nokia_link();
		$xiaomi_link          = $demo_import->get_xiaomi_link();
		$group_link           = $demo_import->get_acf_group_link();
		$view_link            = $demo_import->get_phone_acf_view_link();
		$samsung_article_link = $demo_import->get_samsung_article_link();
		$phones_card_link     = $demo_import->get_phones_acf_card_link();
		$phones_article_link  = $demo_import->get_phones_article_link();

		$heading = array(
			Html_Printer::title_tags( $imported_title ),
			self::bold_paragraph_tags( array( $same_page_title ) ),
		);

		return array_merge(
			$heading,
			self::spaced_link_tags( $samsung_link, $samsung_label ),
			self::spaced_link_tags( $nokia_link, $nokia_label ),
			self::spaced_link_tags( $xiaomi_link, $xiaomi_label ),
			self::spaced_link_tags( $group_link, $group_label ),
			self::spaced_link_tags( $view_link, $view_label ),
			array( self::bold_paragraph_tags( array( $single_title ) ) ),
			self::spaced_link_tags( $samsung_article_link, $samsung_article ),
			array( self::bold_paragraph_tags( $filters_title ) ),
			self::spaced_link_tags( $phones_card_link, $phones_card_label ),
			self::spaced_link_tags( $phones_article_link, $phones_article_name )
		);
	}

	/**
	 * @param string[] $lines
	 *
	 * @return Tag_Tree
	 */
	protected static function bold_paragraph_tags( array $lines ): array {
		$line_tags = array();

		foreach ( $lines as $index => $line ) {
			if ( $index > 0 ) {
				$line_tags[] = array( 'tag' => 'br' );
			}

			$line_tags[] = $line;
		}

		return array( array( 'tag' => 'p' ), array( 'tag' => 'b' ), $line_tags );
	}

	/**
	 * @return Tag_Group
	 */
	protected static function spaced_link_tags( string $url, string $label ): array {
		$link = Html_Printer::link_tags( $url, $label );

		return array(
			$link,
			array( 'tag' => 'br' ),
			array( 'tag' => 'br' ),
		);
	}

	/**
	 * @return Tag_Tree
	 */
	protected static function import_introduction_tags(): array {
		$description_attrs = array( 'class' => 'av-introduction__description' );
		$block_attrs       = array( 'class' => 'av-introduction av-dashboard__block' );
		$import_attrs      = array(
			'class' => 'button button-primary button-large',
			'name'  => '_import',
			'value' => 'import',
		);

		$intro_title   = __( 'Import Demo to Get Started in Seconds', 'acf-views' );
		$import_label  = __( 'Import Demo Now', 'acf-views' );
		$title_tags    = Html_Printer::title_tags( $intro_title );
		$import_button = array(
			array(
				'tag'   => 'button',
				'attrs' => $import_attrs,
			),
			$import_label,
		);

		$description = array(
			array(
				'tag'   => 'p',
				'attrs' => $description_attrs,
			),
			array(
				__(
					"Whether you're new to Advanced Views or just want a quick way to set up the basics, this demo import will automatically create a working example for you.",
					'acf-views'
				),
				array( 'tag' => 'br' ),
				__( "It's perfect for exploring how Layouts, Post Selections, and ACF fields work together.", 'acf-views' ),
			),
		);

		$importing_parts       = array(
			__( 'Click Import Demo and wait a few seconds.', 'acf-views' ),
			__( "When done, you'll see quick links to edit all imported items.", 'acf-views' ),
		);
		$importing_text        = join( ' ', $importing_parts );
		$importing_description = array(
			array(
				'tag'   => 'p',
				'attrs' => $description_attrs,
			),
			$importing_text,
		);

		$same_page_steps = self::ordered_list_tags(
			array(
				__( "Creates 'draft' pages for 'Samsung Galaxy A53', 'Nokia X20' and 'Xiaomi 12T'.", 'acf-views' ),
				__( 'Creates an ACF Field Group called "Phone" assigned to those pages.', 'acf-views' ),
				__( 'Creates a Layout called "Phone" that displays fields from the "Phone" Field Group.', 'acf-views' ),
				__( 'Populates each page’s ACF fields with sample text and adds the Layout shortcode to the page content.', 'acf-views' ),
			)
		);
		$single_steps    = self::ordered_list_tags(
			array(
				__( 'Creates a "draft" page called "Article about Samsung"', 'acf-views' ),
				__( 'Adds a Layout shortcode to the page content with "object-id" parameter pointing to "Samsung Galaxy A53".', 'acf-views' ),
			)
		);
		$selection_steps = self::ordered_list_tags(
			array(
				__( 'Creates a Post Selection called "List of Phones" using the "Phone" Layout and filtering for the phone pages.', 'acf-views' ),
				__( "Create a 'draft' page called 'Most popular phones in 2022' and adds the Post Selection shortcode to the page content.", 'acf-views' ),
			)
		);

		$does_title      = __( 'What this demo does', 'acf-views' );
		$same_page_title = __( "Display page's ACF fields on the same page", 'acf-views' );
		$single_title    = __( 'Display a specific post, page or CPT item with its fields', 'acf-views' );
		$selection_title = __( 'Display multiple items using Post Selections', 'acf-views' );
		$importing_title = __( 'Importing', 'acf-views' );
		$after_title     = __( 'After Import', 'acf-views' );
		$delete_hint     = __( 'A Delete Demo button will appear if you’d like to remove the demo content later.', 'acf-views' );

		return array(
			array(
				'tag'   => 'div',
				'attrs' => $block_attrs,
			),
			array(
				$title_tags,
				$description,
				array( 'tag' => 'hr' ),
				self::bold_paragraph_tags( array( $does_title ) ),
				self::bold_paragraph_tags( array( $same_page_title ) ),
				$same_page_steps,
				self::bold_paragraph_tags( array( $single_title ) ),
				$single_steps,
				self::bold_paragraph_tags( array( $selection_title ) ),
				$selection_steps,
				array( 'tag' => 'hr' ),
				self::bold_paragraph_tags( array( $importing_title ) ),
				$importing_description,
				self::bold_paragraph_tags( array( $after_title ) ),
				array( array( 'tag' => 'p' ), $delete_hint ),
				array( 'tag' => 'hr' ),
				array( 'tag' => 'br' ),
				array( 'tag' => 'br' ),
				$import_button,
			),
		);
	}

	/**
	 * @param string[] $items
	 *
	 * @return Tag_Tree
	 */
	protected static function ordered_list_tags( array $items ): array {
		$list_attrs = array( 'class' => 'av-introduction__description av-introduction__ol' );
		$item_tags  = array_map(
			fn( string $item ) => array( array( 'tag' => 'li' ), $item ),
			$items
		);

		return array(
			array(
				'tag'   => 'ol',
				'attrs' => $list_attrs,
			),
			$item_tags,
		);
	}
}
