<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point;

defined( 'ABSPATH' ) || exit;

use Exception;
use Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
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
		global $wpdb;

		$matched_items = array();

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

		foreach ( $source_posts as $source_post ) {
			// for some reason the field may contain a string.
			$source_post_id = int( $source_post->ID );
			$cpt_settings    = $this->settings_storage->get( $source_post->post_name );

			// filter target mount points
			// as the query was rough and contained common data from all mount points of this provider's items.

			foreach ( $cpt_settings->mount_points as $mount_point_settings ) {
				// without strict comparison, as in the posts array can be strings.
				// @phpcs:ignore
				if ( ! in_array( $current_post_type, $mount_point_settings->post_types, false ) && // @phpstan-ignore-line
					 // @phpcs:ignore
					! in_array( $current_post_id, $mount_point_settings->posts, false ) ) { // @phpstan-ignore-line
					continue;
				}

				if ( ! isset( $matched_items[ $source_post_id ] ) ) {
					$matched_items[ $source_post_id ] = array();
				}

				// several mount points can exist for one source item.
				$matched_items[ $source_post_id ][] = $mount_point_settings;
			}
		}

		return $matched_items;
	}

	public function compose_shortcode( int $post_id, string $shortcode_args ): string {
		if ( Hard_Layout_Cpt::cpt_name() === $this->cpt->cpt_name() &&
			$this->should_claim_point() ) {
			$shortcode_args .= ' mount-point="1"';
		}

		return sprintf( '[%s id="%s"%s]', $this->cpt->shortcode(), $post_id, $shortcode_args );
	}

	protected function should_claim_point(): bool {
		return wp_is_block_theme();
	}
}
