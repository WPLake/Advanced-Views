<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Source;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Attributes\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use WP_Comment;
use WP_Term;

/**
 * Resolves the single Source of a Layout from the (legacy) shortcode args and the context.
 */
final class Source_Resolver {
	private Shortcode_Gutenberg_Block $shortcode_block;

	public function __construct( Shortcode_Gutenberg_Block $shortcode_block ) {
		$this->shortcode_block = $shortcode_block;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return Source|null Null if the 'object-id' argument contains a wrong value.
	 */
	public function resolve( string $item_type, array $attrs, int $current_page_id ): ?Source {
		$object_id  = Attribute_Reader::get_string( $attrs, 'object-id' );
		$user_id    = self::get_user_id( $attrs );
		$term_id    = self::get_term_id( $attrs );
		$comment_id = self::get_comment_id( $attrs );
		$post_slug  = Attribute_Reader::get_string( $attrs, 'post-slug' );

		// enable the 'term' mode by default if we're on a taxonomy page, nothing was set,
		// and it doesn't happen inside the Gutenberg query loop.
		$is_default_term_mode = Layout_Settings::ITEM_TYPE_CUSTOM === $item_type &&
								is_tax() &&
								'' === $object_id &&
								! $this->shortcode_block->is_context_post_set();
		$object_id            = $is_default_term_mode ?
			'term' :
			$object_id;

		$data_post_id = $this->get_data_post_id(
			$object_id,
			$current_page_id,
			$user_id,
			$term_id,
			$comment_id,
			$post_slug
		);

		if ( '' === $data_post_id ) {
			return null;
		}

		$source = new Source();

		$source->set_id( $data_post_id );
		$source->set_is_block( false );
		$source->set_user_id( $user_id );
		$source->set_term_id( $term_id );
		$source->set_comment_id( $comment_id );

		return $source;
	}

	protected function get_data_post_id(
		string $object_id,
		int $current_page_id,
		int $user_id,
		int $term_id,
		int $comment_id,
		string $post_slug
	): string {
		$special_ids = array(
			'options' => fn() => 'options',
			'$user$'  => fn() => sprintf( 'user_%d', $user_id ),
			'user'    => fn() => sprintf( 'user_%d', $user_id ),
			'$term$'  => fn() => sprintf( 'term_%d', $term_id ),
			'term'    => fn() => sprintf( 'term_%d', $term_id ),
			'comment' => fn() => sprintf( 'comment_%d', $comment_id ),
			'post'    => fn() => (string) self::get_post_by_slug( $post_slug ),
		);

		if ( key_exists( $object_id, $special_ids ) ) {
			return $special_ids[ $object_id ]();
		}

		global $post;

		// a. dataPostId from the shortcode argument.

		// 1) page id

		$data_post_id = is_numeric( $object_id ) ?
			(int) $object_id :
			0;

		// 2) page slug (e.g. MetaBox option pages)
		$is_page_slug = 0 === $data_post_id &&
						'' !== $object_id &&
						0 !== self::get_post_by_slug( $object_id );

		if ( $is_page_slug ) {
			// return, we don't need to make any extra checks.
			return $object_id;
		}

		// b. from the Gutenberg query loop.

		if ( $this->shortcode_block->is_context_post_set() ) {
			$data_post_id = $data_post_id > 0 ?
				$data_post_id :
				$this->shortcode_block->get_context_post_id();
		}

		// c. dataPostId from the current loop (WordPress posts, WooCommerce products...).

		$data_post_id = 0 !== $data_post_id ?
			$data_post_id :
			( $post->ID ?? 0 );

		// d. dataPostId from the current page.

		$data_post_id = 0 !== $data_post_id ?
			$data_post_id :
			$current_page_id;

		// validate the ID.

		$is_existing_post = null !== get_post( $data_post_id );

		return (string) ( $is_existing_post ?
			$data_post_id :
			'' );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_term_id( array $attrs ): int {
		// do not use 'get_queried_object_id()' as default value, because PostID can meet some TermId.
		$term_id = $attrs['term-id'] ?? 0;
		$term_id = is_numeric( $term_id ) ?
			(int) $term_id :
			0;

		// validate.
		$is_valid = 0 !== $term_id && get_term( $term_id ) instanceof WP_Term;
		$term_id  = $is_valid ?
			$term_id :
			0;

		if ( 0 === $term_id ) {
			$term_id = self::get_menu_term_id( $attrs );
		}

		// load the default value, only if the 'menu-slug' is missing and current page is a taxonomy page.
		$queried_object   = get_queried_object();
		$is_taxonomy_page = 0 === $term_id && $queried_object instanceof WP_Term;

		return $is_taxonomy_page ?
			$queried_object->term_id :
			$term_id;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_user_id( array $attrs ): int {
		$user_id = $attrs['user-id'] ?? get_current_user_id();
		$user_id = is_numeric( $user_id ) ?
			(int) $user_id :
			0;

		// validate.
		$user = get_user_by( 'id', $user_id );

		return $user->ID ?? 0;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_comment_id( array $attrs ): int {
		$comment_id = $attrs['comment-id'] ?? 0;
		$comment_id = is_numeric( $comment_id ) ?
			(int) $comment_id :
			0;

		// validate.
		$is_valid = 0 !== $comment_id && get_comment( $comment_id ) instanceof WP_Comment;

		return $is_valid ?
			$comment_id :
			0;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_menu_term_id( array $attrs ): int {
		$menu_slug = Attribute_Reader::get_string( $attrs, 'menu-slug' );
		$is_filled = strlen( $menu_slug ) > 0;
		$menu_term = $is_filled ?
			get_term_by( 'slug', $menu_slug, 'nav_menu' ) :
			null;

		return $menu_term->term_id ?? 0;
	}

	// get_page_by_path() requires post_type, but we don't know it, so use the direct query.
	protected static function get_post_by_slug( string $slug ): int {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT * FROM $wpdb->posts WHERE post_name = %s",
			$slug
		);
		// phpcs:ignore
		$post = $wpdb->get_row( $query );

		$is_valid_post = is_object( $post ) &&
						property_exists( $post, 'ID' ) &&
						is_numeric( $post->ID );

		return $is_valid_post ?
			(int) $post->ID :
			0;
	}
}
