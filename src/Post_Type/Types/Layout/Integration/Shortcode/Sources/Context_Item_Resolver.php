<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use WP_Term;

/**
 * Resolves the item IDs provided by the current request context (Gutenberg query loop, main query, queried object).
 */
final class Context_Item_Resolver {
	private Shortcode_Gutenberg_Block $shortcode_block;

	public function __construct( Shortcode_Gutenberg_Block $shortcode_block ) {
		$this->shortcode_block = $shortcode_block;
	}

	public static function resolve_page_id(): int {
		// equals to 0 on WooCommerce Shop Page, but in this case pageID can't be gotten with built-in WP functions
		// also works in the taxonomy case.
		return get_queried_object_id();
	}

	public static function resolve_current_user_id(): int {
		return get_current_user_id();
	}

	/**
	 * The order: the query loop, the current loop (WordPress posts, WooCommerce products...), the page.
	 */
	public function resolve_post_id(): int {
		$candidates = array(
			$this->resolve_query_loop_post_id(),
			self::resolve_loop_post_id(),
			self::resolve_page_id(),
		);
		$non_zero   = array_filter(
			$candidates,
			static fn( int $id ): bool => 0 !== $id
		);

		return (int) current( $non_zero );
	}

	public function resolve_query_loop_post_id(): int {
		return $this->shortcode_block->is_context_post_set() ?
			$this->shortcode_block->get_context_post_id() :
			0;
	}

	public static function resolve_loop_post_id(): int {
		global $post;

		return $post->ID ?? 0;
	}

	public static function resolve_queried_term_id(): int {
		$queried_object = get_queried_object();

		return $queried_object instanceof WP_Term ?
			$queried_object->term_id :
			0;
	}

	/**
	 * @return int[]
	 */
	public static function resolve_main_query_post_ids(): array {
		global $wp_query;

		$posts    = $wp_query->posts ?? array();
		$post_ids = array_map(
			fn( $post ): int => (int) $post->ID,
			$posts
		);

		return array_values( $post_ids );
	}
}
