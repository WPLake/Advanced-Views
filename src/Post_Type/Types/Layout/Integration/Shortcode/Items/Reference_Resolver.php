<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Query\Provider\Post_Provider;
use WP_Term;

/**
 * Resolves the item IDs of a Layout from already parsed references (post selections, menu slugs).
 */
final class Reference_Resolver {
	private Post_Provider $post_provider;

	public function __construct( Post_Provider $post_provider ) {
		$this->post_provider = $post_provider;
	}

	public static function resolve_menu_term_id( string $menu_slug ): ?int {
		$is_filled = strlen( $menu_slug ) > 0;
		$menu_term = $is_filled ?
			get_term_by( 'slug', $menu_slug, 'nav_menu' ) :
			null;

		return $menu_term instanceof WP_Term ?
			$menu_term->term_id :
			null;
	}

	/**
	 * @param string[] $selection_ids
	 *
	 * @return int[]
	 */
	public function resolve_selection_posts( array $selection_ids, Query_Context $context ): array {
		$post_ids = array();

		foreach ( $selection_ids as $selection_id ) {
			$selection_post_ids = $this->post_provider->get_posts( $selection_id, $context );
			$post_ids           = array_merge( $post_ids, $selection_post_ids );
		}

		return $post_ids;
	}

	/**
	 * @param string[] $menu_slugs
	 *
	 * @return int[]
	 */
	public static function resolve_menu_ids( array $menu_slugs ): array {
		$menu_ids = array();

		foreach ( $menu_slugs as $menu_slug ) {
			$menu = get_term_by( 'slug', $menu_slug, 'nav_menu' );

			if ( $menu instanceof WP_Term ) {
				$menu_ids[] = $menu->term_id;
			}
		}

		return $menu_ids;
	}
}
