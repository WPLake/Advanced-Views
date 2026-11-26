<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use WP_Post;
use WP_Term;

/**
 * Provides the item IDs of the current request context (Gutenberg query loop, main query, queried object).
 */
final class Request_Context_Reader {
	private Shortcode_Gutenberg_Block $shortcode_block;

	public function __construct( Shortcode_Gutenberg_Block $shortcode_block ) {
		$this->shortcode_block = $shortcode_block;
	}

	/**
	 * @return int|null Null on pages without a queried object (e.g. the WooCommerce Shop Page, where it can't be
	 *                  gotten with built-in WP functions). Also works in the taxonomy case.
	 */
	public static function get_queried_object_id(): ?int {
		return self::get_positive_id( get_queried_object_id() );
	}

	/**
	 * @return int|null Null for a guest.
	 */
	public static function get_current_user_id(): ?int {
		return self::get_positive_id( get_current_user_id() );
	}

	/**
	 * The order: the query loop, the current loop (WordPress posts, WooCommerce products...), the queried page.
	 *
	 * @return int|null Null if the context has no post.
	 */
	public function get_post_id(): ?int {
		$candidates = array(
			$this->get_query_loop_post_id(),
			self::get_loop_post_id(),
			self::get_queried_post_id(),
		);
		$known_ids  = array_values(
			array_filter(
				$candidates,
				static fn( ?int $id ): bool => null !== $id
			)
		);

		return $known_ids[0] ?? null;
	}

	public function get_query_loop_post_id(): ?int {
		return $this->shortcode_block->is_context_post_set() ?
			self::get_positive_id( $this->shortcode_block->get_context_post_id() ) :
			null;
	}

	/**
	 * Unlike the queried object ID, it's null if the queried object isn't a post (e.g. on a taxonomy page the ID is a term's one).
	 */
	public static function get_queried_post_id(): ?int {
		$queried_object = get_queried_object();

		return $queried_object instanceof WP_Post ?
			$queried_object->ID :
			null;
	}

	public static function get_loop_post_id(): ?int {
		global $post;

		return self::get_positive_id( $post->ID ?? 0 );
	}

	public static function get_queried_term_id(): ?int {
		$queried_object = get_queried_object();

		return $queried_object instanceof WP_Term ?
			$queried_object->term_id :
			null;
	}

	/**
	 * @return int[]
	 */
	public static function get_main_query_post_ids(): array {
		global $wp_query;

		$posts    = $wp_query->posts ?? array();
		$post_ids = array_map(
			fn( $post ): int => (int) $post->ID,
			$posts
		);

		return array_values( $post_ids );
	}

	protected static function get_positive_id( int $id ): ?int {
		return $id > 0 ?
			$id :
			null;
	}
}
