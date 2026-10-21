<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Environment\External_Links;
use Org\Wplake\Advanced_Views\Template\Html\Tags_Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Tags_Renderer
 * @phpstan-import-type Tag_Group from Tags_Renderer
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
class Postbox_Printer {
	protected Html_Printer $html;

	public function __construct( Html_Printer $html ) {
		$this->html = $html;
	}

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

		$shortcode_name = $public_cpt->shortcode();
		$shortcode      = sprintf( '[%s name="%s" %s="%s"]', $shortcode_name, $entry_name, 'id', $unique_id );
		$tags           = $this->shortcode_tags( $shortcode, $is_short, $is_single );

		Tags_Renderer::print_tags( $tags );
	}

	public function print_postbox_upgrade(): void {
		$description = __(
			'Enable advanced features like repeater fields, AJAX pagination, sliders, and lightboxes.',
			'acf-views'
		);

		$button_label = __( 'Upgrade to Pro', 'acf-views' );
		$tags         = $this->support_postbox_tags( $description, External_Links::PRO_VERSION_URL, $button_label );

		Tags_Renderer::print_tags( $tags );
	}

	public function print_postbox_support(): void {
		$description = __(
			"We're here to help you with your questions. Support is handled through Wordpress.org",
			'acf-views'
		);

		$button_label = __( 'Get support', 'acf-views' );
		$tags         = $this->support_postbox_tags(
			$description,
			'https://wordpress.org/support/plugin/acf-views/',
			$button_label
		);

		Tags_Renderer::print_tags( $tags );
	}

	/**
	 * @return Tag_Chain
	 */
	protected function shortcode_tags( string $shortcode, bool $is_short, bool $is_single ): array {
		$type    = $is_short ?
			'short' :
			'full';
		$code_attrs      = array( 'class' => 'av-sortcodes__code av-shortcodes__code--type--short' );
		$container_class = sprintf( 'av-shortcodes av-shortcodes--type--%s', $type );
		$container_attrs = array( 'class' => $container_class );
		$code            = array(
			$this->html->tag( 'span', $code_attrs ),
			$shortcode,
		);
		$details         = $is_short ?
			array() :
			$this->shortcode_details_tags( $is_single );

		return array(
			$this->html->tag( 'av-shortcodes', $container_attrs ),
			array( $code, array( $details ) ),
		);
	}

	/**
	 * @return Tag_Group
	 */
	protected function shortcode_details_tags( bool $is_single ): array {
		$copy_attrs  = array(
			'class'       => 'av-shortcodes__copy-button button button-primary button-large',
			'data-target' => '.av-shortcodes__code--type--short',
		);
		$copy_button = array(
			$this->html->tag( 'button', $copy_attrs ),
			__( 'Copy Shortcode', 'acf-views' ),
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

		$here_label     = __( 'here', 'acf-views' );
		$roles_hint_gap = sprintf( '%s ', $roles_hint );
		$docs_link      = $this->html->link_tags(
			'https://docs.advanced-views.com/layouts/embedding-shortcode',
			$here_label
		);

		$roles_link = array(
			$this->html->tag( 'span' ),
			array(
				$roles_hint_gap,
				$docs_link,
				'.',
			),
		);

		$gutenberg_question = __( 'Using Gutenberg?', 'acf-views' );
		$elementor_question = __( 'Using Elementor?', 'acf-views' );

		return array(
			$copy_button,
			$this->embed_hint_tags( $gutenberg_question, $gutenberg_answer ),
			$this->embed_hint_tags( $elementor_question, $elementor_answer ),
			$roles_link,
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function embed_hint_tags( string $question, string $answer ): array {
		return array(
			$this->html->tag( 'p' ),
			array( $question, $this->html->tag( 'br' ), $answer ),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function support_postbox_tags( string $description, string $url, string $button_label ): array {
		$button = $this->button_link_tags( $url, $button_label );

		return array(
			$this->html->tag( 'div' ),
			array(
				array( $this->html->tag( 'p' ), $description ),
				$button,
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function button_link_tags( string $url, string $label ): array {
		$link_attrs = array(
			'class'  => 'button button-primary button-large',
			'href'   => esc_url( $url ),
			'target' => '_blank',
		);

		return array(
			$this->html->tag( 'a', $link_attrs ),
			$label,
		);
	}
}
