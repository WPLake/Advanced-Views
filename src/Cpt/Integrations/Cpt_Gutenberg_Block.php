<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt\Integrations;

defined( 'ABSPATH' ) || exit;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * Gutenberg-only logic shared by every Gutenberg block backed by a Cpt_Settings_Storage (Layout, Post Selection...) -
 * edition-agnostic logic (preview markup, item list...) lives on Cpt_Integration_Block instead.
 */
final class Cpt_Gutenberg_Block {
	private function __construct() {
	}

	/**
	 * @param mixed[] $categories
	 *
	 * @return mixed[]
	 */
	public static function add_block_category( array $categories ): array {
		return array_merge(
			array(
				array(
					'slug'  => Cpt_Integration_Block::CATEGORY,
					'title' => __( 'Advanced Views', 'acf-views' ),
				),
			),
			$categories
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function get_supports(): array {
		return array(
			'customClassName' => false,
			'customCSS'       => false,
		);
	}

	/**
	 * @return array<string,array{type:string,default:string}>
	 */
	public static function get_attribute_declarations(): array {
		return array(
			'class'            => array(
				'type'    => 'string',
				'default' => '',
			),
			'userWithRoles'    => array(
				'type'    => 'string',
				'default' => '',
			),
			'userWithoutRoles' => array(
				'type'    => 'string',
				'default' => '',
			),
			'customArguments'  => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}

	/**
	 * @param array<string,mixed> $attributes
	 *
	 * @return array<string,string>
	 */
	public static function parse_attributes( array $attributes ): array {
		return array(
			'class'              => string( $attributes, 'class' ),
			'user-with-roles'    => string( $attributes, 'userWithRoles' ),
			'user-without-roles' => string( $attributes, 'userWithoutRoles' ),
			'custom-arguments'   => string( $attributes, 'customArguments' ),
		);
	}

	/**
	 * @return string[]
	 */
	public static function get_block_js_dependencies(): array {
		return array(
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-server-side-render',
			'wp-i18n',
			'wp-api-fetch',
		);
	}
}
