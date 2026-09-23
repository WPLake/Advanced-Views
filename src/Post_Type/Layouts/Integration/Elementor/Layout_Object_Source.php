<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * PHP mirror of origin/assets/lite/admin/js/integrations/gutenberg/layout/sourceList.ts's resolveObjectId() - the Gutenberg
 * editor resolves the Object Source controls into the shortcode's 'object-id'/lookup attrs client-side and
 * saves the result as a plain block attribute, but Elementor's render() runs entirely server-side against its
 * own saved control values, so that mapping needs a PHP implementation of its own.
 */
abstract class Layout_Object_Source {
	public static function add_source_controls( Widget_Base $widget ): void {
		$id_field_help = __(
			'To pull it from another field, add that field inside the current Layout instead.',
			'acf-views'
		);

		$widget->add_control(
			'object_source',
			array(
				'label'   => __( 'Object Source', 'acf-views' ),
				'type'    => Controls_Manager::SELECT2,
				'default' => 'post',
				'options' => array(
					'post'    => __( 'Post', 'acf-views' ),
					'options' => __( 'Custom options page (ACF/MB)', 'acf-views' ),
					'user'    => __( 'User', 'acf-views' ),
					'term'    => __( 'Term', 'acf-views' ),
					'menu'    => __( 'Menu', 'acf-views' ),
					'comment' => __( 'Comment', 'acf-views' ),
				),
			)
		);

		$widget->add_control(
			'post_lookup',
			array(
				'label'     => __( 'Look Up Post By', 'acf-views' ),
				'type'      => Controls_Manager::SELECT2,
				'default'   => 'current',
				'options'   => array(
					'current' => __( 'Current Post', 'acf-views' ),
					'id'      => __( 'Post ID', 'acf-views' ),
					'slug'    => __( 'Post Slug', 'acf-views' ),
				),
				'condition' => array( 'object_source' => 'post' ),
			)
		);

		$widget->add_control(
			'post_id',
			array(
				'label'       => __( 'Post ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => $id_field_help,
				'condition'   => array(
					'object_source' => 'post',
					'post_lookup'   => 'id',
				),
			)
		);

		$widget->add_control(
			'post_slug',
			array(
				'label'       => __( 'Post Slug', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => $id_field_help,
				'condition'   => array(
					'object_source' => 'post',
					'post_lookup'   => 'slug',
				),
			)
		);

		$widget->add_control(
			'user_id',
			array(
				'label'       => __( 'User ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Leave empty to use the current user.', 'acf-views' )
								. ' ' . $id_field_help,
				'condition'   => array( 'object_source' => 'user' ),
			)
		);

		$widget->add_control(
			'term_id',
			array(
				'label'       => __( 'Term ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Leave empty to use the current term on a term page.', 'acf-views' )
								. ' ' . $id_field_help,
				'condition'   => array( 'object_source' => 'term' ),
			)
		);

		$widget->add_control(
			'menu_slug',
			array(
				'label'       => __( 'Menu Slug', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => $id_field_help,
				'condition'   => array( 'object_source' => 'menu' ),
			)
		);

		$widget->add_control(
			'comment_id',
			array(
				'label'       => __( 'Comment ID', 'acf-views' ),
				'type'        => Controls_Manager::TEXT,
				'description' => $id_field_help,
				'condition'   => array( 'object_source' => 'comment' ),
			)
		);
	}

	/**
	 * @param array<string,mixed> $settings Elementor's own control values (object_source, post_lookup, post_id).
	 */
	public static function resolve_object_id( array $settings ): string {
		$object_source = string( $settings, 'object_source' );

		if ( 'post' === $object_source ) {
			switch ( string( $settings, 'post_lookup' ) ) {
				case 'slug':
					return 'post';
				case 'id':
					return string( $settings, 'post_id' );
				default:
					// the "Current Post" lookup - an empty 'object-id' falls back to the current object.
					return '';
			}
		}

		return $object_source;
	}

	/**
	 * @param array<string,mixed> $settings Elementor's own control values.
	 *
	 * @return array<string,string>
	 */
	public static function get_lookup_attributes( array $settings ): array {
		$attrs = array(
			'post-slug'  => string( $settings, 'post_slug' ),
			'user-id'    => string( $settings, 'user_id' ),
			'term-id'    => string( $settings, 'term_id' ),
			'menu-slug'  => string( $settings, 'menu_slug' ),
			'comment-id' => string( $settings, 'comment_id' ),
		);

		return array_filter(
			$attrs,
			fn( string $value ): bool => strlen( $value ) > 0
		);
	}
}
