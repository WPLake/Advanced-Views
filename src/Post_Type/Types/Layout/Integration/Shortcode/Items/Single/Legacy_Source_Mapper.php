<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Single;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser\Request_Context_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Lookup\Existing_Item_Filter;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Lookup\Reference_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

/**
 * Maps the legacy shortcode arguments (object-id, user-id...) of a custom-data Layout into its single Source.
 */
final class Legacy_Source_Mapper {
	private Shortcode_Gutenberg_Block $shortcode_block;
	private Request_Context_Reader $context_provider;

	public function __construct( Shortcode_Gutenberg_Block $shortcode_block, Request_Context_Reader $context_provider ) {
		$this->shortcode_block  = $shortcode_block;
		$this->context_provider = $context_provider;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return Source|null Null if the 'object-id' argument contains a wrong value.
	 */
	public function map_source( array $attrs ): ?Source {
		$object_id = $this->resolve_object_id( $attrs );
		$post_slug = Attribute_Reader::get_string( $attrs, 'post-slug' );

		$user_argument = self::get_user_argument( $attrs );
		$user_id       = null !== $user_argument ?
			( Existing_Item_Filter::validate_user_ids( array( $user_argument ) )[0] ?? 0 ) :
			( Request_Context_Reader::get_current_user_id() ?? 0 );

		$term_id = self::resolve_term_id( $attrs );

		$comment_item_id = Attribute_Reader::get_int( $attrs, 'comment-id' );
		$comment_id      = Existing_Item_Filter::validate_comment_ids( array( $comment_item_id ) )[0] ?? 0;

		$data_post_id = $this->get_data_post_id(
			$object_id,
			$user_id,
			$term_id,
			$comment_id,
			$post_slug
		);
		$is_resolved  = strlen( $data_post_id ) > 0;

		if ( $is_resolved ) {
			return self::create_source( $data_post_id, $user_id, $term_id, $comment_id );
		}

		return null;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function resolve_term_id( array $attrs ): int {
		$term_item_id = Attribute_Reader::get_int( $attrs, 'term-id' );
		$menu_slug    = Attribute_Reader::get_string( $attrs, 'menu-slug' );

		if ( 0 !== $term_item_id ) {
			return Existing_Item_Filter::validate_term_ids( array( $term_item_id ) )[0] ?? 0;
		}

		return strlen( $menu_slug ) > 0 ?
			Reference_Resolver::resolve_menu_term_id( $menu_slug ) ?? 0 :
			Request_Context_Reader::get_queried_term_id() ?? 0;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function resolve_object_id( array $attrs ): string {
		$object_id = self::get_object_id( $attrs );

		// enable the 'term' mode by default if we're on a taxonomy page, nothing was set,
		// and it doesn't happen inside the Gutenberg query loop.
		$is_default_term_mode = is_tax() &&
								0 === strlen( $object_id ) &&
								! $this->shortcode_block->is_context_post_set();

		return $is_default_term_mode ?
			'term' :
			$object_id;
	}

	protected function get_data_post_id(
		string $object_id,
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
						strlen( $object_id ) > 0 &&
						0 !== self::get_post_by_slug( $object_id );

		if ( $is_page_slug ) {
			// return, we don't need to make any extra checks.
			return $object_id;
		}

		// an invalid argument is not replaced with the context default, only an absent one is.
		$post_id = strlen( $object_id ) > 0 ?
			( Existing_Item_Filter::validate_post_ids( array( $item_id ) )[0] ?? null ) :
			$this->context_provider->get_post_id();

		return null !== $post_id ?
			(string) $post_id :
			'';
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function get_object_id( array $attrs ): string {
		$object_id    = Attribute_Reader::get_string( $attrs, 'object-id' );
		$options_page = Attribute_Reader::parse_list( $attrs['options-page'] ?? null )[0] ?? '';
		$is_filled    = strlen( $options_page ) > 0;

		return $is_filled ?
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

	protected static function create_source( string $data_post_id, int $user_id, int $term_id, int $comment_id ): Source {
		$source = new Source();

		$source->set_id( $data_post_id );
		$source->set_is_block( false );
		$source->set_user_id( $user_id );
		$source->set_term_id( $term_id );
		$source->set_comment_id( $comment_id );

		return $source;
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
