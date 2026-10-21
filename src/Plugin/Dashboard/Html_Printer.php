<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Tools\Demo_Importer;
use Org\Wplake\Advanced_Views\Plugin\Environment\External_Links;
use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Tags_Renderer
 * @phpstan-import-type Tag_Group from Tags_Renderer
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
class Html_Printer {
	public function print_postbox_shortcode(
		string $unique_id,
		bool $is_short,
		Public_Cpt $public_cpt,
		string $entry_name,
		bool $is_single,
		bool $is_internal_usage_only = false
	): void {
		if ( $is_internal_usage_only ) {
			echo esc_html( __( '(internal use only)', 'acf-views' ) );

			return;
		}

		$shortcode = sprintf( '[%s name="%s" %s="%s"]', $public_cpt->shortcode(), $entry_name, 'id', $unique_id );

		Tags_Renderer::print_tags( $this->shortcode_tags( $shortcode, $is_short, $is_single ) );
	}

	public function print_postbox_upgrade(): void {
		$description = __(
			'Enable advanced features like repeater fields, AJAX pagination, sliders, and lightboxes.',
			'acf-views'
		);

		Tags_Renderer::print_tags(
			$this->support_postbox_tags(
				$description,
				External_Links::PRO_VERSION_URL,
				__( 'Upgrade to Pro', 'acf-views' )
			)
		);
	}

	public function print_postbox_support(): void {
		$description = __(
			"We're here to help you with your questions. Support is handled through Wordpress.org",
			'acf-views'
		);

		Tags_Renderer::print_tags(
			$this->support_postbox_tags(
				$description,
				'https://wordpress.org/support/plugin/acf-views/',
				__( 'Get support', 'acf-views' )
			)
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 */
	public function print_dashboard_header( string $name, string $version, array $tabs ): void {
		Tags_Renderer::print_tags( $this->toolbar_tags( $name, $version, $tabs ) );
	}

	public function print_dashboard_import(
		bool $is_has_demo_objects,
		string $form_nonce,
		bool $is_with_form_message,
		Demo_Importer $demo_import
	): void {
		Tags_Renderer::print_tags(
			$this->dashboard_form_tags(
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
	 * @param Tag_Group $blocks
	 *
	 * @return Tag_Chain
	 */
	protected function dashboard_form_tags( string $page, string $form_nonce, array $blocks ): array {
		return array(
			array(
				'tag'   => 'form',
				'attrs' => array(
					'action' => '',
					'method' => 'post',
					'class'  => 'av-dashboard',
				),
			),
			array(
				$this->hidden_input_tags( '_av-page', $page ),
				$this->hidden_input_tags( '_wpnonce', $form_nonce ),
				array(
					$this->tag( 'div', array( 'class' => 'av-dashboard__main' ) ),
					$blocks,
				),
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function hidden_input_tags( string $name, string $value ): array {
		return array(
			array(
				'tag'   => 'input',
				'attrs' => array(
					'type'  => 'hidden',
					'name'  => $name,
					'value' => $value,
				),
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function shortcode_tags( string $shortcode, bool $is_short, bool $is_single ): array {
		$type    = $is_short ?
			'short' :
			'full';
		$code    = array(
			$this->tag( 'span', array( 'class' => 'av-sortcodes__code av-shortcodes__code--type--short' ) ),
			$this->escaped_text( $shortcode ),
		);
		$details = $is_short ?
			array() :
			$this->shortcode_details_tags( $is_single );

		return array(
			$this->tag( 'av-shortcodes', array( 'class' => 'av-shortcodes av-shortcodes--type--' . $type ) ),
			array( $code, array( $details ) ),
		);
	}

	/**
	 * @return Tag_Group
	 */
	protected function shortcode_details_tags( bool $is_single ): array {
		$copy_button = array(
			$this->tag(
				'button',
				array(
					'class'       => 'av-shortcodes__copy-button button button-primary button-large',
					'data-target' => '.av-shortcodes__code--type--short',
				)
			),
			$this->escaped_text( __( 'Copy Shortcode', 'acf-views' ) ),
		);

		$gutenberg_answer = $is_single ?
			__( 'Embed with the AV Post Selection block.', 'acf-views' ) :
			__( 'Embed with the AV Layout block.', 'acf-views' );
		$elementor_answer = $is_single ?
			__( 'Embed with the AV Post Selection widget.', 'acf-views' ) :
			__( 'Embed with the AV Layout widget.', 'acf-views' );
		$roles_hint       = $is_single ?
			__( 'See how to limit visibility by roles', 'acf-views' ) :
			__( 'See how to load from other sources or limit visibility by roles', 'acf-views' );

		$roles_link = array(
			$this->tag( 'span' ),
			array(
				$this->escaped_text( $roles_hint . ' ' ),
				$this->link_tags(
					'https://docs.advanced-views.com/layouts/embedding-shortcode',
					__( 'here', 'acf-views' )
				),
				$this->escaped_text( '.' ),
			),
		);

		return array(
			$copy_button,
			$this->embed_hint_tags( __( 'Using Gutenberg?', 'acf-views' ), $gutenberg_answer ),
			$this->embed_hint_tags( __( 'Using Elementor?', 'acf-views' ), $elementor_answer ),
			$roles_link,
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function embed_hint_tags( string $question, string $answer ): array {
		return array(
			$this->tag( 'p' ),
			array( $this->escaped_text( $question ), $this->tag( 'br' ), $this->escaped_text( $answer ) ),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function support_postbox_tags( string $description, string $url, string $button_label ): array {
		return array(
			$this->tag( 'div' ),
			array(
				array( $this->tag( 'p' ), $this->escaped_text( $description ) ),
				$this->button_link_tags( $url, $button_label ),
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function button_link_tags( string $url, string $label ): array {
		return array(
			$this->tag(
				'a',
				array(
					'class'  => 'button button-primary button-large',
					'href'   => esc_url( $url ),
					'target' => '_blank',
				)
			),
			$this->escaped_text( $label ),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function link_tags( string $url, string $label ): array {
		return array(
			$this->tag(
				'a',
				array(
					'target' => '_blank',
					'href'   => esc_url( $url ),
				)
			),
			$this->escaped_text( $label ),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Chain
	 */
	protected function toolbar_tags( string $name, string $version, array $tabs ): array {
		$title = array(
			$this->tag( 'h2', array( 'class' => 'av-toolbar__title' ) ),
			array(
				array( $this->tag( 'i', array( 'class' => 'av-toolbar__icon dashicons dashicons-layout' ) ) ),
				array( $this->tag( 'span' ), $this->escaped_text( $name ) ),
				array( $this->tag( 'span', array( 'class' => 'av-toolbar__title-version' ) ), $this->escaped_text( 'v.' . $version ) ),
			),
		);

		return array(
			$this->tag( 'div', array( 'class' => 'av-toolbar' ) ),
			array(
				$title,
				$this->toolbar_block_tags( 'left', 'isLeftBlock', $tabs ),
				$this->toolbar_block_tags( 'right', 'isRightBlock', $tabs ),
			),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Chain
	 */
	protected function toolbar_block_tags( string $type, string $block_key, array $tabs ): array {
		$block_tabs = array_filter(
			$tabs,
			fn( array $tab ) => isset( $tab[ $block_key ] )
		);

		return array(
			$this->tag( 'div', array( 'class' => 'av-toolbar__block av-toolbar__block--type--' . $type ) ),
			array_map(
				fn( array $tab ) => $this->toolbar_tab_tags( $tab ),
				array_values( $block_tabs )
			),
		);
	}

	/**
	 * @param array<string,mixed> $tab
	 *
	 * @return Tag_Chain
	 */
	protected function toolbar_tab_tags( array $tab ): array {
		$classes = array_filter(
			array(
				'av-toolbar__tab',
				bool( $tab, 'isActive' ) ?
					'av-toolbar__tab--active' :
					'',
				bool( $tab, 'isSecondary' ) ?
					'av-toolbar__tab--secondary' :
					'',
				string( $tab, 'class' ),
			),
			fn( string $class ) => '' !== $class
		);
		$attrs   = array(
			'class'  => array_values( $classes ),
			'href'   => esc_url( string( $tab, 'url' ) ),
			'target' => bool( $tab, 'isBlank' ) ?
				'_blank' :
				'_self',
		);

		if ( key_exists( 'style', $tab ) ) {
			$attrs['style'] = string( $tab, 'style' );
		}

		$icon = key_exists( 'iconClasses', $tab ) ?
			array( $this->tag( 'i', array( 'class' => string( $tab, 'iconClasses' ) ) ) ) :
			array();

		return array(
			$this->tag( 'a', $attrs ),
			array(
				array( $this->tag( 'span' ), $this->escaped_text( string( $tab, 'label' ) ) ),
				$icon,
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function import_result_block_tags( bool $is_has_demo_objects, Demo_Importer $demo_import ): array {
		$delete_button = $is_has_demo_objects ?
			array(
				$this->tag( 'br' ),
				$this->tag( 'br' ),
				array(
					$this->tag(
						'button',
						array(
							'class' => 'button button-primary button-large av-dashboard__button av-dashboard__button--red',
							'name'  => '_delete',
							'value' => 'delete',
						)
					),
					$this->escaped_text( __( 'Delete imported objects', 'acf-views' ) ),
				),
			) :
			array();

		return array(
			$this->tag( 'div', array( 'class' => 'av-introduction av-dashboard__block av-dashboard__block--medium' ) ),
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

			return array( $this->title_tags( $message ) );
		}

		return array(
			$this->title_tags( __( 'Request is failed.', 'acf-views' ) ),
			$this->tag( 'br' ),
			$this->tag( 'br' ),
			$this->escaped_text( $demo_import->get_error() ),
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
				$this->title_tags( __( 'Imported items', 'acf-views' ) ),
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
	 * @return Tag_Chain
	 */
	protected function title_tags( string $title ): array {
		return array(
			$this->tag( 'p', array( 'class' => 'av-introduction__title' ) ),
			$this->escaped_text( $title ),
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
				$line_tags[] = $this->tag( 'br' );
			}

			$line_tags[] = $this->escaped_text( $line );
		}

		return array( $this->tag( 'p' ), $this->tag( 'b' ), $line_tags );
	}

	/**
	 * @return Tag_Group
	 */
	protected function spaced_link_tags( string $url, string $label ): array {
		return array(
			$this->link_tags( $url, $label ),
			$this->tag( 'br' ),
			$this->tag( 'br' ),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function import_introduction_tags(): array {
		$description = array(
			$this->tag( 'p', array( 'class' => 'av-introduction__description' ) ),
			array(
				$this->escaped_text(
					__(
						"Whether you're new to Advanced Views or just want a quick way to set up the basics, this demo import will automatically create a working example for you.",
						'acf-views'
					)
				),
				$this->tag( 'br' ),
				$this->escaped_text( __( "It's perfect for exploring how Layouts, Post Selections, and ACF fields work together.", 'acf-views' ) ),
			),
		);

		$importing_description = array(
			$this->tag( 'p', array( 'class' => 'av-introduction__description' ) ),
			$this->escaped_text(
				__( 'Click Import Demo and wait a few seconds.', 'acf-views' ) . ' ' .
				__( "When done, you'll see quick links to edit all imported items.", 'acf-views' )
			),
		);

		return array(
			$this->tag( 'div', array( 'class' => 'av-introduction av-dashboard__block' ) ),
			array(
				$this->title_tags( __( 'Import Demo to Get Started in Seconds', 'acf-views' ) ),
				$description,
				$this->tag( 'hr' ),
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
				$this->tag( 'hr' ),
				$this->bold_paragraph_tags( array( __( 'Importing', 'acf-views' ) ) ),
				$importing_description,
				$this->bold_paragraph_tags( array( __( 'After Import', 'acf-views' ) ) ),
				array(
					$this->tag( 'p' ),
					$this->escaped_text( __( 'A Delete Demo button will appear if you’d like to remove the demo content later.', 'acf-views' ) ),
				),
				$this->tag( 'hr' ),
				$this->tag( 'br' ),
				$this->tag( 'br' ),
				array(
					$this->tag(
						'button',
						array(
							'class' => 'button button-primary button-large',
							'name'  => '_import',
							'value' => 'import',
						)
					),
					$this->escaped_text( __( 'Import Demo Now', 'acf-views' ) ),
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
			$this->tag( 'ol', array( 'class' => 'av-introduction__description av-introduction__ol' ) ),
			array_map(
				fn( string $item ) => array( $this->tag( 'li' ), $this->escaped_text( $item ) ),
				$items
			),
		);
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return Tag
	 */
	protected function tag( string $tag, array $attrs = array() ): array {
		return array(
			'tag'   => $tag,
			'attrs' => $attrs,
		);
	}

	protected function escaped_text( string $text ): callable {
		return fn() => print( esc_html( $text ) );
	}
}
