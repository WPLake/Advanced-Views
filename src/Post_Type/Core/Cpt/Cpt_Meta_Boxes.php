<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Postbox_Printer;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\stack;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Template\Html\nest;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html
 * @phpstan-import-type Html_Nest from Html
 */
abstract class Cpt_Meta_Boxes extends Actor_Base implements Actor {
	private Postbox_Printer $postbox;
	private Plugin $plugin;

	public function __construct( Postbox_Printer $postbox, Plugin $plugin ) {
		$this->postbox = $postbox;
		$this->plugin  = $plugin;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	public function add_meta_boxes(): void {
		$cpt_name      = $this->get_cpt_name();
		$screens       = array( $cpt_name );
		$support_title = __( 'Help & Documentation', 'acf-views' );

		add_meta_box(
			'acf-views_support',
			$support_title,
			fn() => $this->postbox->print_postbox_support(),
			$screens,
			'side',
			'low'
		);

		if ( ! $this->plugin->is_pro_version() ) {
			$upgrade_title = __( 'Unlock with Pro', 'acf-views' );

			add_meta_box(
				'acf-views_upgrade',
				$upgrade_title,
				fn() => $this->postbox->print_postbox_upgrade(),
				$screens,
				'side',
				'low'
			);
		}
	}

	public function print_mount_points( Cpt_Settings $cpt_settings ): void {
		$post_types = self::get_mount_post_types( $cpt_settings );
		$post_links = self::get_mount_post_links( $cpt_settings );

		$post_types_line = self::create_post_types_line( $post_types );
		$pages_line      = self::create_pages_line( $post_links, array() !== $post_types );

		$tags = stack( $post_types_line, $pages_line );

		Html_Renderer::print( $tags );
	}

	abstract protected function get_cpt_name(): string;

	protected function get_postbox(): Postbox_Printer {
		return $this->postbox;
	}

	/**
	 * @return string[]
	 */
	protected static function get_mount_post_types( Cpt_Settings $cpt_settings ): array {
		$post_types = array();

		foreach ( $cpt_settings->mount_points as $mount_point ) {
			$post_types = array_merge( $post_types, $mount_point->post_types );
		}

		return array_unique( $post_types );
	}

	/**
	 * @return list<string|Html_Nest> Links separated by ', ' items
	 */
	protected static function get_mount_post_links( Cpt_Settings $cpt_settings ): array {
		$posts = array();

		foreach ( $cpt_settings->mount_points as $mount_point ) {
			$posts = array_merge( $posts, $mount_point->posts );
		}

		$posts      = array_unique( $posts );
		$post_links = array();

		foreach ( $posts as $post ) {
			// the ', ' goes between the links.
			if ( array() !== $post_links ) {
				$post_links[] = ', ';
			}

			$post_links[] = self::create_post_link( $post );
		}

		return $post_links;
	}

	/**
	 * @param string[] $post_types
	 *
	 * @return Html_Node|null
	 */
	protected static function create_post_types_line( array $post_types ) {
		if ( array() !== $post_types ) {
			$label = __( 'Post Types:', 'acf-views' );
			$types = join( ', ', $post_types );
			$parts = array(
				$label,
				$types,
			);

			return join( ' ', $parts );
		}

		return null;
	}

	/**
	 * @param list<string|Html_Nest> $post_links
	 *
	 * @return Html_Node|null
	 */
	protected static function create_pages_line( array $post_links, bool $has_post_types ) {
		if ( array() !== $post_links ) {
			$label = __( 'Pages:', 'acf-views' );

			return stack(
				$has_post_types ?
					tag( 'br' ) :
					null,
				sprintf( '%s ', $label ),
				...$post_links
			);
		}

		return null;
	}

	/**
	 * @param int|\WP_Post $post
	 *
	 * @return Html_Nest
	 */
	protected static function create_post_link( $post ) {
		$post_url = get_the_permalink( $post );
		$post_url = is_string( $post_url ) ?
			$post_url :
			'';

		return nest(
			tag(
				'a',
				array(
					'target' => '_blank',
					'href'   => $post_url,
				)
			),
			get_the_title( $post )
		);
	}
}
