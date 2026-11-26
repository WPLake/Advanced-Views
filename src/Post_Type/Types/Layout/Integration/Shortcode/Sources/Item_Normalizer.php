<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources;

defined( 'ABSPATH' ) || exit;

use WP_Comment;
use WP_Post;
use WP_Term;
use WP_User;

/**
 * Normalizes parsed item IDs: keeps an ID only if its entity exists, otherwise returns 0.
 */
final class Item_Normalizer {
	public static function normalize_post_id( int $item_id ): int {
		$is_valid = 0 !== $item_id && get_post( $item_id ) instanceof WP_Post;

		return $is_valid ?
			$item_id :
			0;
	}

	public static function normalize_user_id( int $item_id ): int {
		$is_valid = 0 !== $item_id && get_user_by( 'id', $item_id ) instanceof WP_User;

		return $is_valid ?
			$item_id :
			0;
	}

	public static function normalize_term_id( int $item_id ): int {
		$is_valid = 0 !== $item_id && get_term( $item_id ) instanceof WP_Term;

		return $is_valid ?
			$item_id :
			0;
	}

	public static function normalize_comment_id( int $item_id ): int {
		$is_valid = 0 !== $item_id && get_comment( $item_id ) instanceof WP_Comment;

		return $is_valid ?
			$item_id :
			0;
	}

	/**
	 * @param string[] $ids
	 *
	 * @return int[]
	 */
	public static function normalize_ids( array $ids ): array {
		$ids       = array_map( 'intval', $ids );
		$valid_ids = array_filter(
			$ids,
			fn( int $id ): bool => $id > 0
		);

		return array_values( $valid_ids );
	}
}
