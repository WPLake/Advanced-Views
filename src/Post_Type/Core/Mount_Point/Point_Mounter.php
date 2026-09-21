<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings;
use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;
use WP_Post;

/**
 * Common class for both Layout and Post_Selection
 */
class Point_Mounter extends Hookable implements Hooks_Interface {
	/**
	 * @var Point_Provider[]
	 */
	private array $point_providers;

	/**
	 * @param Point_Provider[] $point_providers
	 */
	public function __construct( array $point_providers ) {
		$this->point_providers = $point_providers;
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		self::add_filter(
			'the_content',
			function ( $content ) {
				$queried_object = get_queried_object();

				if ( is_string( $content ) &&
					$queried_object instanceof WP_Post &&
					$this->is_supported_place() ) {
					return $this->mount_all( $queried_object, $content );
				}

				return $content;
			}
		);
	}

	protected function mount_all( WP_Post $queried_object, string $content ): string {
		foreach ( $this->point_providers as $point_provider ) {
			$matched_items = $point_provider->match_items( $queried_object->post_type, $queried_object->ID );

			foreach ( $matched_items as $source_post_id => $mount_points ) {
				foreach ( $mount_points as $mount_point ) {
					$content = $this->mount_point( $point_provider, $source_post_id, $mount_point, $content );
				}
			}
		}

		return $content;
	}

	/**
	 * @param bool $is_run_shortcode Can be false for tests
	 */
	protected function mount_point(
		Point_Provider $point_provider,
		int $source_post_id,
		Mount_Point_Settings $mount_point_settings,
		string $content,
		bool $is_run_shortcode = true
	): string {
		$replacement_range = $this->find_replacement_range( $mount_point_settings, $content );

		// mountPoint not found (mistake), just skip.
		if ( null === $replacement_range ) {
			return $content;
		}

		$shortcode = $this->prepare_shortcode( $point_provider, $source_post_id, $mount_point_settings, $is_run_shortcode );

		return substr_replace( $content, $shortcode, $replacement_range['offset'], $replacement_range['length'] );
	}

	/**
	 * @return ?array{offset:int,length:int}
	 */
	protected function find_replacement_range( Mount_Point_Settings $mount_point_settings, string $content ): ?array {
		$point_strlen = strlen( $mount_point_settings->mount_point );
		$is_point_set = $point_strlen > 0;

		$start_of_marker_index = $is_point_set ?
			strpos( $content, $mount_point_settings->mount_point ) :
			0;

		if ( false === $start_of_marker_index ) {
			return null;
		}

		$end_of_marker_index = $is_point_set ?
			$start_of_marker_index + $point_strlen :
			strlen( $content );
		$marker_length       = $is_point_set ?
			$point_strlen :
			strlen( $content );

		$offset = 0;
		$length = 0;

		switch ( $mount_point_settings->mount_position ) {
			case Mount_Point_Settings::MOUNT_POSITION_BEFORE:
				$offset = $start_of_marker_index;
				break;
			case Mount_Point_Settings::MOUNT_POSITION_AFTER:
				$offset = $end_of_marker_index;
				break;
			case Mount_Point_Settings::MOUNT_POSITION_INSTEAD:
				$offset = $start_of_marker_index;
				$length = $marker_length;
				break;
		}

		return array(
			'offset' => $offset,
			'length' => $length,
		);
	}

	protected function prepare_shortcode(
		Point_Provider $point_provider,
		int $source_post_id,
		Mount_Point_Settings $mount_point_settings,
		bool $is_run_shortcode
	): string {
		$shortcode_args = strlen( $mount_point_settings->shortcode_args ) > 0 ?
			' ' . $mount_point_settings->shortcode_args :
			'';
		$shortcode      = $point_provider->compose_shortcode( $source_post_id, $shortcode_args );

		return $is_run_shortcode ?
			do_shortcode( $shortcode ) :
			$shortcode;
	}

	/**
	 * Make sure the_content inside the main loop,
	 * otherwise can be called within sidebars, etc, and there is no opportunity to check it
	 * (as the filter has only 1 argument and global variables are untouched)
	 * more https://developer.wordpress.org/reference/hooks/the_content/.
	 */
	protected function is_supported_place(): bool {
		return is_singular() &&
				in_the_loop() &&
				is_main_query();
	}
}
