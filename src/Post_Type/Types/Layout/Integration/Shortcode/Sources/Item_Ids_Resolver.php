<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Query\Provider\Post_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use WP_Term;

/**
 * Resolves the item IDs of a Multiple-format Layout from the shortcode arguments and the context.
 */
final class Item_Ids_Resolver {
	private Post_Provider $post_provider;

	public function __construct( Post_Provider $post_provider ) {
		$this->post_provider = $post_provider;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return string|null Null if the attrs are valid.
	 */
	public function get_error( string $item_type, array $attrs ): ?string {
		$posts      = Attribute_Reader::parse_list( $attrs['posts'] ?? null );
		$selections = Attribute_Reader::parse_list( $attrs['post-selections'] ?? null );

		$has_posts      = count( $posts ) > 0;
		$has_selections = count( $selections ) > 0;
		$is_conflict    = Layout_Settings::ITEM_TYPE_POST === $item_type && $has_posts && $has_selections;

		return $is_conflict ?
			__( "'posts' and 'post-selections' arguments can't be used together", 'acf-views' ) :
			null;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return int[]
	 */
	public function resolve( string $item_type, array $attrs, Query_Context $context ): array {
		if ( Layout_Settings::ITEM_TYPE_POST === $item_type ) {
			return $this->resolve_posts( $attrs, $context );
		}

		if ( Layout_Settings::ITEM_TYPE_MENU === $item_type ) {
			$menu_slugs = Attribute_Reader::parse_list( $attrs['menus'] ?? null );

			return self::resolve_menus( $menu_slugs );
		}

		$arg_name = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? '';
		$ids      = Attribute_Reader::parse_list( $attrs[ $arg_name ] ?? null );

		// users, terms and comments have no context list in this release.
		return self::to_ids( $ids );
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return int[]
	 */
	protected function resolve_posts( array $attrs, Query_Context $context ): array {
		$posts      = Attribute_Reader::parse_list( $attrs['posts'] ?? null );
		$selections = Attribute_Reader::parse_list( $attrs['post-selections'] ?? null );

		$has_posts      = count( $posts ) > 0;
		$has_selections = count( $selections ) > 0;

		if ( $has_selections ) {
			return $this->resolve_selections( $selections, $context );
		}

		return $has_posts ?
			self::to_ids( $posts ) :
			self::get_main_query_post_ids();
	}

	/**
	 * @param string[] $selection_ids
	 *
	 * @return int[]
	 */
	protected function resolve_selections( array $selection_ids, Query_Context $context ): array {
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
	protected static function resolve_menus( array $menu_slugs ): array {
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
	 * @return int[]
	 */
	protected static function get_main_query_post_ids(): array {
		global $wp_query;

		$posts    = $wp_query->posts ?? array();
		$post_ids = array_map(
			fn( $post ): int => (int) $post->ID,
			$posts
		);

		return array_values( $post_ids );
	}

	/**
	 * @param string[] $ids
	 *
	 * @return int[]
	 */
	protected static function to_ids( array $ids ): array {
		$ids       = array_map( 'intval', $ids );
		$valid_ids = array_filter(
			$ids,
			fn( int $id ): bool => $id > 0
		);

		return array_values( $valid_ids );
	}
}
