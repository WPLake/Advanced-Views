<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Query\Provider\Post_Provider;
use WP_Term;

/**
 * Resolves the item IDs of a Layout from already parsed values and the context.
 */
final class Item_Resolver {
	private Post_Provider $post_provider;
	private Context_Item_Resolver $context_resolver;

	public function __construct( Post_Provider $post_provider, Context_Item_Resolver $context_resolver ) {
		$this->post_provider    = $post_provider;
		$this->context_resolver = $context_resolver;
	}

	/**
	 * @param int $item_id 0 means the argument is absent, so the context post is used.
	 */
	public function resolve_post_id( int $item_id ): int {
		$post_id = 0 !== $item_id ?
			$item_id :
			$this->context_resolver->resolve_post_id();

		return Item_Normalizer::normalize_post_id( $post_id );
	}

	/**
	 * @param int|null $item_id Null means the current user.
	 */
	public static function resolve_user_id( ?int $item_id ): int {
		return Item_Normalizer::normalize_user_id( $item_id ?? Context_Item_Resolver::resolve_current_user_id() );
	}

	/**
	 * @param string $menu_slug The fallback menu, used when the term ID is invalid.
	 */
	public static function resolve_term_id( int $item_id, string $menu_slug = '' ): int {
		$term_id = Item_Normalizer::normalize_term_id( $item_id );

		return 0 !== $term_id ?
			$term_id :
			self::resolve_menu_term_id( $menu_slug );
	}

	public static function resolve_menu_term_id( string $menu_slug ): int {
		$is_filled = strlen( $menu_slug ) > 0;
		$menu_term = $is_filled ?
			get_term_by( 'slug', $menu_slug, 'nav_menu' ) :
			null;

		return $menu_term->term_id ?? Context_Item_Resolver::resolve_queried_term_id();
	}

	/**
	 * @param string[] $posts
	 * @param string[] $selection_ids
	 *
	 * @return int[]
	 */
	public function resolve_post_ids( array $posts, array $selection_ids, Query_Context $context ): array {
		if ( count( $selection_ids ) > 0 ) {
			return $this->resolve_selection_posts( $selection_ids, $context );
		}

		return count( $posts ) > 0 ?
			Item_Normalizer::normalize_ids( $posts ) :
			Context_Item_Resolver::resolve_main_query_post_ids();
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

	/**
	 * @param string[] $selection_ids
	 *
	 * @return int[]
	 */
	protected function resolve_selection_posts( array $selection_ids, Query_Context $context ): array {
		$post_ids = array();

		foreach ( $selection_ids as $selection_id ) {
			$selection_post_ids = $this->post_provider->get_posts( $selection_id, $context );
			$post_ids           = array_merge( $post_ids, $selection_post_ids );
		}

		return $post_ids;
	}
}
