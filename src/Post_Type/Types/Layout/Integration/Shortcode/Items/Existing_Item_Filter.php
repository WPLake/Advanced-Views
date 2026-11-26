<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items;

defined( 'ABSPATH' ) || exit;

use WP_Comment;
use WP_Post;
use WP_Term;
use WP_User;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\intOrNull;

/**
 * Validates parsed item IDs: keeps only the IDs whose entity exists.
 */
final class Existing_Item_Filter {
	/**
	 * @param array<mixed> $ids
	 *
	 * @return int[] Only the numeric positive IDs.
	 */
	public static function validate_ids( array $ids ): array {
		$ids = array_map(
			fn( $id ): ?int => intOrNull( $id ),
			$ids
		);

		$valid_ids = array_filter(
			$ids,
			fn( ?int $id ): bool => is_int( $id ) && $id > 0
		);

		return array_values( $valid_ids );
	}

	/**
	 * @param array<mixed> $ids
	 *
	 * @return int[] Only the IDs of existing posts.
	 */
	public static function validate_post_ids( array $ids ): array {
		return self::filter_existing( $ids, fn( int $id ): bool => get_post( $id ) instanceof WP_Post );
	}

	/**
	 * @param array<mixed> $ids
	 *
	 * @return int[] Only the IDs of existing users.
	 */
	public static function validate_user_ids( array $ids ): array {
		return self::filter_existing( $ids, fn( int $id ): bool => get_user_by( 'id', $id ) instanceof WP_User );
	}

	/**
	 * @param array<mixed> $ids
	 *
	 * @return int[] Only the IDs of existing terms.
	 */
	public static function validate_term_ids( array $ids ): array {
		return self::filter_existing( $ids, fn( int $id ): bool => get_term( $id ) instanceof WP_Term );
	}

	/**
	 * @param array<mixed> $ids
	 *
	 * @return int[] Only the IDs of existing comments.
	 */
	public static function validate_comment_ids( array $ids ): array {
		return self::filter_existing( $ids, fn( int $id ): bool => get_comment( $id ) instanceof WP_Comment );
	}

	/**
	 * @param array<mixed>       $ids
	 * @param callable(int):bool $is_existing
	 *
	 * @return int[]
	 */
	protected static function filter_existing( array $ids, callable $is_existing ): array {
		$existing_ids = array_filter(
			self::validate_ids( $ids ),
			$is_existing
		);

		return array_values( $existing_ids );
	}
}
