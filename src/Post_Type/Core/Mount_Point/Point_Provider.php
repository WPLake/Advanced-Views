<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point;

defined( 'ABSPATH' ) || exit;

use Exception;
use Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use WP_Post;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

class Point_Provider {
	private Cpt_Settings_Storage $settings_storage;
	private Public_Cpt $cpt;

	public function __construct( Cpt_Settings_Storage $settings_storage, Public_Cpt $cpt ) {
		$this->settings_storage = $settings_storage;
		$this->cpt              = $cpt;
	}

	/**
	 * Return array with structure: sourcePostId => Mount_Point_Settings[]
	 *
	 * @return array<int, Mount_Point_Settings[]>
	 * @throws Exception
	 */
	public function match_items( string $current_post_type, int $current_post_id ): array {
		$matched_items = array();

		foreach ( $this->query_source_posts( $current_post_type, $current_post_id ) as $source_post ) {
			// for some reason the field may contain a string.
			$source_post_id = int( $source_post->ID );
			$mount_points   = $this->match_mount_points( $source_post, $current_post_type, $current_post_id );

			if ( array() !== $mount_points ) {
				$matched_items[ $source_post_id ] = $mount_points;
			}
		}

		return $matched_items;
	}

	public function compose_shortcode( int $post_id, string $shortcode_args ): string {
		return sprintf( '[%s id="%s"%s]', $this->cpt->shortcode(), $post_id, $shortcode_args );
	}

	/**
	 * @return WP_Post[]
	 */
	protected function query_source_posts( string $current_post_type, int $current_post_id ): array {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT * from {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'
					  AND (FIND_IN_SET(%s,post_excerpt) > 0 OR FIND_IN_SET(%s,post_excerpt) > 0)",
			$this->cpt->cpt_name(),
			$current_post_type,
			$current_post_id
		);
		// @phpcs:ignore
		$source_posts = $wpdb->get_results( $query );

		// direct $wpdb queries return strings for int columns, wrap into get_post to get right types.
		/**
		 * @var WP_Post[] $source_posts
		 */
		$source_posts = array_map(
			fn( $source_post ) => get_post( $source_post->ID ),
			$source_posts
		);

		return $source_posts;
	}

	/**
	 * The query is rough and returns common data from all mount points of the source item,
	 * so narrow it down to the ones actually matching the current post.
	 *
	 * @return Mount_Point_Settings[]
	 */
	protected function match_mount_points( WP_Post $source_post, string $current_post_type, int $current_post_id ): array {
		$matched_mount_points = array();
		$cpt_settings         = $this->settings_storage->get( $source_post->post_name );

		foreach ( $cpt_settings->mount_points as $mount_point_settings ) {
			// without strict comparison, as in the posts array can be strings.
			// @phpcs:ignore
			if ( ! in_array( $current_post_type, $mount_point_settings->post_types, false ) && // @phpstan-ignore-line
				 // @phpcs:ignore
				! in_array( $current_post_id, $mount_point_settings->posts, false ) ) { // @phpstan-ignore-line
				continue;
			}

			$matched_mount_points[] = $mount_point_settings;
		}

		return $matched_mount_points;
	}
}
