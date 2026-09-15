<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Types\Layouts\Integrations\Gutenberg;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Cpt\Integrations\Cpt_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Cpt\Integrations\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

final class Layout_Gutenberg_Block extends Hookable implements Hooks_Interface {
	// prefixed by the plugin name for wp.org Plugin directory discover.
	const NAME = Plugin::PRODUCT_SLUG . '/layout';

	private Plugin $plugin;
	private Cpt_Item_Picker $item_picker;
	private Cpt_Gutenberg_Block $cpt_block;
	private Route_Detector $route_detector;

	public function __construct(
		Plugin $plugin,
		Cpt_Item_Picker $item_picker,
		Cpt_Gutenberg_Block $cpt_block
	) {
		$this->plugin      = $plugin;
		$this->item_picker = $item_picker;
		$this->cpt_block   = $cpt_block;
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		$this->route_detector = $route_detector;

		self::add_action( 'init', array( $this, 'register_block' ) );

		if ( $route_detector->is_admin_route() ) {
			self::add_filter( 'block_categories_all', array( Cpt_Gutenberg_Block::class, 'add_block_category' ) );
			self::add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		}
	}

	public function register_block(): void {
		register_block_type(
			__DIR__ . '/block.json',
			array(
				'category'        => Cpt_Item_Picker::CATEGORY,
				'supports'        => Cpt_Gutenberg_Block::get_supports(),
				'attributes'      => array_merge(
					self::get_attribute_declarations(),
					Cpt_Gutenberg_Block::get_attribute_declarations()
				),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	public function render_block( array $attributes ): string {
		$attrs = self::parse_attributes( $attributes );

		return $this->cpt_block->render_block( $attrs, $this->route_detector->is_admin_route() );
	}

	public function enqueue_editor_assets(): void {
		wp_enqueue_script(
			self::NAME,
			$this->plugin->get_assets_url( 'js/admin/blocks/layout-block.min.js' ),
			Cpt_Gutenberg_Block::get_block_js_dependencies(),
			$this->plugin->get_version(),
			true
		);

		wp_localize_script(
			self::NAME,
			'avfLayoutBlock',
			array(
				'blockName'  => self::NAME,
				'itemPicker' => $this->item_picker->get_js_data(),
			)
		);
	}

	/**
	 * @param array<string,mixed> $attributes
	 *
	 * @return array<string,string>
	 */
	protected static function parse_attributes( array $attributes ): array {
		$attrs = array_merge(
			array(
				'id'        => string( $attributes, 'layoutId' ),
				'object-id' => string( $attributes, 'objectId' ),
			),
			self::parse_lookup_attributes( $attributes ),
			Cpt_Gutenberg_Block::parse_attributes( $attributes ),
		);

		return array_filter(
			$attrs,
			fn( string $value ): bool => strlen( $value ) > 0
		);
	}

	/**
	 * @param array<string,mixed> $attributes
	 *
	 * @return array<string,string>
	 */
	protected static function parse_lookup_attributes( array $attributes ): array {
		return array(
			'post-slug'  => string( $attributes, 'postSlug' ),
			'user-id'    => string( $attributes, 'userId' ),
			'term-id'    => string( $attributes, 'termId' ),
			'menu-slug'  => string( $attributes, 'menuSlug' ),
			'comment-id' => string( $attributes, 'commentId' ),
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	protected static function get_attribute_declarations(): array {
		return array(
			'layoutId'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'objectId'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'objectSource' => array(
				'type'    => 'string',
				'default' => 'post',
			),
			'postLookup'   => array(
				'type'    => 'string',
				'default' => 'current',
			),
			'postId'       => array(
				'type'    => 'string',
				'default' => '',
			),
			'userId'       => array(
				'type'    => 'string',
				'default' => '',
			),
			'termId'       => array(
				'type'    => 'string',
				'default' => '',
			),
			'menuSlug'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'postSlug'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'commentId'    => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}
}
