<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Gutenberg\Shortcode_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use WP_Comment;
use WP_Term;

/**
 * Resolves the single Source of a typed Layout from its item arguments (posts, users...) and the context.
 */
final class Item_Source_Resolver {
	private Shortcode_Gutenberg_Block $shortcode_block;

	public function __construct( Shortcode_Gutenberg_Block $shortcode_block ) {
		$this->shortcode_block = $shortcode_block;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return Source|null Null if the item can't be resolved.
	 */
	public function resolve( string $item_type, array $attrs, int $current_page_id ): ?Source {
		$arg_name = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? '';
		$first_id = Attribute_Reader::parse_list( $attrs[ $arg_name ] ?? null )[0] ?? '';
		$item_id  = is_numeric( $first_id ) ?
			(int) $first_id :
			0;

		$resolvers = array(
			Layout_Settings::ITEM_TYPE_POST    => fn() => $this->get_post_id( $item_id, $current_page_id ),
			Layout_Settings::ITEM_TYPE_USER    => fn() => self::get_user_id( '' === $first_id ? null : $item_id ),
			Layout_Settings::ITEM_TYPE_TERM    => fn() => self::get_term_id( $item_id ),
			Layout_Settings::ITEM_TYPE_COMMENT => fn() => self::get_comment_id( $item_id ),
			Layout_Settings::ITEM_TYPE_MENU    => fn() => self::get_menu_term_id( $first_id ),
		);

		if ( ! key_exists( $item_type, $resolvers ) ) {
			return null;
		}

		$resolved_id = $resolvers[ $item_type ]();
		$is_post     = Layout_Settings::ITEM_TYPE_POST === $item_type;

		return $is_post && 0 === $resolved_id ?
			null :
			Source::make_item( $item_type, $resolved_id );
	}

	public function get_post_id( int $item_id, int $current_page_id ): int {
		global $post;

		// the Gutenberg query loop.
		$context_post_id = $this->shortcode_block->is_context_post_set() ?
			$this->shortcode_block->get_context_post_id() :
			0;

		// the order: the argument, the query loop, the current loop (WordPress posts, WooCommerce products...), the page.
		$candidates = array( $item_id, $context_post_id, $post->ID ?? 0, $current_page_id );
		$post_id    = (int) current( array_filter( $candidates, static fn( int $id ): bool => 0 !== $id ) );

		return null !== get_post( $post_id ) ?
			$post_id :
			0;
	}

	/**
	 * @param int|null $item_id Null means the current user.
	 */
	public static function get_user_id( ?int $item_id ): int {
		$user_id = $item_id ?? get_current_user_id();
		$user    = get_user_by( 'id', $user_id );

		return $user->ID ?? 0;
	}

	/**
	 * @param string $menu_slug The fallback menu, used when the term ID is invalid.
	 */
	public static function get_term_id( int $item_id, string $menu_slug = '' ): int {
		$is_valid = 0 !== $item_id && get_term( $item_id ) instanceof WP_Term;

		return $is_valid ?
			$item_id :
			self::get_menu_term_id( $menu_slug );
	}

	public static function get_menu_term_id( string $menu_slug ): int {
		$menu_term = '' !== $menu_slug ?
			get_term_by( 'slug', $menu_slug, 'nav_menu' ) :
			null;

		return $menu_term->term_id ?? self::get_queried_term_id();
	}

	public static function get_comment_id( int $item_id ): int {
		$is_valid = 0 !== $item_id && get_comment( $item_id ) instanceof WP_Comment;

		return $is_valid ?
			$item_id :
			0;
	}

	protected static function get_queried_term_id(): int {
		$queried_object = get_queried_object();

		return $queried_object instanceof WP_Term ?
			$queried_object->term_id :
			0;
	}
}
