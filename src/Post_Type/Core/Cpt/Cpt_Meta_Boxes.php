<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html_Printer;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;

defined( 'ABSPATH' ) || exit;

abstract class Cpt_Meta_Boxes extends Actor_Base implements Actor {
	private Html_Printer $html;
	private Plugin $plugin;

	public function __construct( Html_Printer $html, Plugin $plugin ) {
		$this->html   = $html;
		$this->plugin = $plugin;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	abstract protected function get_cpt_name(): string;

	protected function get_html(): Html_Printer {
		return $this->html;
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'acf-views_support',
			__( 'Help & Documentation', 'acf-views' ),
			function (): void {
				$this->html->print_postbox_support();
			},
			array(
				$this->get_cpt_name(),
			),
			'side',
			'low'
		);

		if ( ! $this->plugin->is_pro_version() ) {
			add_meta_box(
				'acf-views_upgrade',
				__( 'Unlock with Pro', 'acf-views' ),
				function (): void {
					$this->html->print_postbox_upgrade();
				},
				array(
					$this->get_cpt_name(),
				),
				'side',
				'low'
			);
		}
	}

	public function print_mount_points( Cpt_Settings $cpt_settings ): void {
		$post_types      = array();
		$safe_post_links = array();

		foreach ( $cpt_settings->mount_points as $mount_point ) {
			$post_types      = array_merge( $post_types, $mount_point->post_types );
			$safe_post_links = array_merge( $safe_post_links, $mount_point->posts );
		}

		$post_types      = array_unique( $post_types );
		$safe_post_links = array_unique( $safe_post_links );

		$post_links = array();

		foreach ( $safe_post_links as $post ) {
			$post_url = get_the_permalink( $post );
			$post_url = false !== $post_url ?
				$post_url :
				'';

			// the ', ' goes between the links.
			if ( array() !== $post_links ) {
				$post_links[] = fn() => print( ', ' );
			}

			$post_links[] = array(
				array(
					'tag'   => 'a',
					'attrs' => array(
						'target' => '_blank',
						'href'   => esc_url( $post_url ),
					),
				),
				fn() => print( esc_html( get_the_title( $post ) ) ),
			);
		}

		$post_types_line = array() !== $post_types ?
			array(
				fn() => print( esc_html( __( 'Post Types:', 'acf-views' ) . ' ' . join( ', ', $post_types ) ) ),
			) :
			array();
		$pages_line      = array() !== $post_links ?
			array(
				array() !== $post_types ?
					array( 'tag' => 'br' ) :
					array(),
				fn() => print( __( 'Pages:', 'acf-views' ) . ' ' ),
				array( $post_links ),
			) :
			array();

		Tags_Renderer::print_tag_group(
			array( $post_types_line, array( $pages_line ) )
		);
	}
}
