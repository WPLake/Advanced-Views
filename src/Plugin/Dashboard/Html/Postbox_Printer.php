<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Environment\External_Links;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\stack;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Template\Html\nest;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Tag from Html
 * @phpstan-import-type Html_Stack from Html
 * @phpstan-import-type Html_Nest from Html
 */
class Postbox_Printer {
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
		$tags           = self::shortcode_tags( $shortcode, $is_short, $is_single );

		Html_Renderer::print( $tags );
	}

	public function print_postbox_upgrade(): void {
		$description = __(
			'Enable advanced features like repeater fields, AJAX pagination, sliders, and lightboxes.',
			'acf-views'
		);

		$button_label = __( 'Upgrade to Pro', 'acf-views' );
		$tags         = self::support_postbox_tags( $description, External_Links::PRO_VERSION_URL, $button_label );

		Html_Renderer::print( $tags );
	}

	public function print_postbox_support(): void {
		$description = __(
			"We're here to help you with your questions. Support is handled through Wordpress.org",
			'acf-views'
		);

		$button_label = __( 'Get support', 'acf-views' );
		$tags         = self::support_postbox_tags(
			$description,
			'https://wordpress.org/support/plugin/acf-views/',
			$button_label
		);

		Html_Renderer::print( $tags );
	}

	/**
	 * @return Html_Nest
	 */
	protected static function shortcode_tags( string $shortcode, bool $is_short, bool $is_single ): array {
		$type            = $is_short ?
			'short' :
			'full';
		$code_attrs      = array( 'class' => 'av-sortcodes__code av-shortcodes__code--type--short' );
		$container_class = sprintf( 'av-shortcodes av-shortcodes--type--%s', $type );
		$container_attrs = array( 'class' => $container_class );
		$code            = nest(
			tag(
				'span',
				$code_attrs
			),
			$shortcode
		);
		$details         = $is_short ?
			null :
			self::shortcode_details_tags( $is_single );

		return nest(
			tag(
				'av-shortcodes',
				$container_attrs
			),
			stack( $code, $details )
		);
	}

	/**
	 * @return Html_Stack
	 */
	protected static function shortcode_details_tags( bool $is_single ): array {
		$copy_attrs  = array(
			'class'       => 'av-shortcodes__copy-button button button-primary button-large',
			'data-target' => '.av-shortcodes__code--type--short',
		);
		$copy_button = nest(
			tag(
				'button',
				$copy_attrs
			),
			__( 'Copy Shortcode', 'acf-views' )
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
		$docs_link      = Html_Printer::link_tags(
			'https://docs.advanced-views.com/layouts/embedding-shortcode',
			$here_label
		);

		$roles_link = nest(
			tag( 'span' ),
			stack(
				$roles_hint_gap,
				$docs_link,
				'.'
			)
		);

		$gutenberg_question = __( 'Using Gutenberg?', 'acf-views' );
		$elementor_question = __( 'Using Elementor?', 'acf-views' );

		return stack(
			$copy_button,
			self::embed_hint_tags( $gutenberg_question, $gutenberg_answer ),
			self::embed_hint_tags( $elementor_question, $elementor_answer ),
			$roles_link
		);
	}

	/**
	 * @return Html_Nest
	 */
	protected static function embed_hint_tags( string $question, string $answer ): array {
		return nest(
			tag( 'p' ),
			stack(
				$question,
				tag( 'br' ),
				$answer
			)
		);
	}

	/**
	 * @return Html_Nest
	 */
	protected static function support_postbox_tags( string $description, string $url, string $button_label ): array {
		$button    = self::button_link_tags( $url, $button_label );
		$paragraph = nest(
			tag( 'p' ),
			$description
		);

		return nest(
			tag( 'div' ),
			stack(
				$paragraph,
				$button
			)
		);
	}

	/**
	 * @return Html_Nest
	 */
	protected static function button_link_tags( string $url, string $label ): array {
		$link_attrs = array(
			'class'  => 'button button-primary button-large',
			'href'   => $url,
			'target' => '_blank',
		);

		return nest(
			tag(
				'a',
				$link_attrs
			),
			$label
		);
	}
}
