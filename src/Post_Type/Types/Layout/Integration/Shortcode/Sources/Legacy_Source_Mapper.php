<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

/**
 * Maps the legacy shortcode arguments (object-id, user-id...) of a custom-data Layout into its single Source.
 */
final class Legacy_Source_Mapper {
	private Shortcode_Gutenberg_Block $shortcode_block;
	private Item_Source_Resolver $item_source_resolver;

	public function __construct( Shortcode_Gutenberg_Block $shortcode_block, Item_Source_Resolver $item_source_resolver ) {
		$this->shortcode_block      = $shortcode_block;
		$this->item_source_resolver = $item_source_resolver;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return Source|null Null if the 'object-id' argument contains a wrong value.
	 */
	public function map( array $attrs, int $current_page_id ): ?Source {
		$object_id  = self::get_object_id( $attrs );
		$user_id    = Item_Source_Resolver::get_user_id( self::get_user_argument( $attrs ) );
		$term_id    = Item_Source_Resolver::get_term_id(
			Attribute_Reader::get_int( $attrs, 'term-id' ),
			Attribute_Reader::get_string( $attrs, 'menu-slug' )
		);
		$comment_id = Item_Source_Resolver::get_comment_id( Attribute_Reader::get_int( $attrs, 'comment-id' ) );
		$post_slug  = Attribute_Reader::get_string( $attrs, 'post-slug' );

		// enable the 'term' mode by default if we're on a taxonomy page, nothing was set,
		// and it doesn't happen inside the Gutenberg query loop.
		$is_default_term_mode = is_tax() &&
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

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_object_id( array $attrs ): string {
		$object_id    = Attribute_Reader::get_string( $attrs, 'object-id' );
		$options_page = Attribute_Reader::parse_list( $attrs['options-page'] ?? null )[0] ?? '';

		return '' !== $options_page ?
			$options_page :
			$object_id;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return int|null Null if the argument is missing, so the current user is used.
	 */
	protected static function get_user_argument( array $attrs ): ?int {
		return isset( $attrs['user-id'] ) ?
			Attribute_Reader::get_int( $attrs, 'user-id' ) :
			null;
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

		// a numeric ID, or a page slug (e.g. MetaBox option pages).
		$item_id      = is_numeric( $object_id ) ?
			(int) $object_id :
			0;
		$is_page_slug = 0 === $item_id &&
						'' !== $object_id &&
						0 !== self::get_post_by_slug( $object_id );

		if ( $is_page_slug ) {
			// return, we don't need to make any extra checks.
			return $object_id;
		}

		$post_id = $this->item_source_resolver->get_post_id( $item_id, $current_page_id );

		return 0 !== $post_id ?
			(string) $post_id :
			'';
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
