<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Markup;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Environment\External_Links;
use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;

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
	 * @return Tag_Chain
	 */
	protected function shortcode_tags( string $shortcode, bool $is_short, bool $is_single ): array {
		$type    = $is_short ?
			'short' :
			'full';
		$code    = array(
			$this->html->tag( 'span', array( 'class' => 'av-sortcodes__code av-shortcodes__code--type--short' ) ),
			$shortcode,
		);
		$details = $is_short ?
			array() :
			$this->shortcode_details_tags( $is_single );

		return array(
			$this->html->tag( 'av-shortcodes', array( 'class' => 'av-shortcodes av-shortcodes--type--' . $type ) ),
			array( $code, array( $details ) ),
		);
	}

	/**
	 * @return Tag_Group
	 */
	protected function shortcode_details_tags( bool $is_single ): array {
		$copy_button = array(
			$this->html->tag(
				'button',
				array(
					'class'       => 'av-shortcodes__copy-button button button-primary button-large',
					'data-target' => '.av-shortcodes__code--type--short',
				)
			),
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

		$roles_link = array(
			$this->html->tag( 'span' ),
			array(
				$roles_hint . ' ',
				$this->html->link_tags(
					'https://docs.advanced-views.com/layouts/embedding-shortcode',
					__( 'here', 'acf-views' )
				),
				'.',
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
			$this->html->tag( 'p' ),
			array( $question, $this->html->tag( 'br' ), $answer ),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function support_postbox_tags( string $description, string $url, string $button_label ): array {
		return array(
			$this->html->tag( 'div' ),
			array(
				array( $this->html->tag( 'p' ), $description ),
				$this->button_link_tags( $url, $button_label ),
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	protected function button_link_tags( string $url, string $label ): array {
		return array(
			$this->html->tag(
				'a',
				array(
					'class'  => 'button button-primary button-large',
					'href'   => esc_url( $url ),
					'target' => '_blank',
				)
			),
			$label,
		);
	}
}
