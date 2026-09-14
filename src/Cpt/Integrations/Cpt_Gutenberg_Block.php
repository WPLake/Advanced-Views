<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt\Integrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Cpt\Base\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * Gutenberg-only logic shared by every Gutenberg block backed by a Cpt_Settings_Storage (Layout, Post Selection...).
 * One instance per CPT, built with that CPT's Cpt_Settings_Storage/Public_Cpt/Shortcode_Renderer/Front_Assets -
 * holds render_block()'s isLoaded()-gated dispatch (identical across every CPT's Gutenberg block, previously
 * duplicated in each one) so a concrete block only has to pass it the current request's attrs/is_admin_route.
 */
final class Cpt_Gutenberg_Block {
	private Cpt_Settings_Storage $settings_storage;
	private Public_Cpt $cpt;
	private Cpt_Renderer $renderer;

	public function __construct(
		Cpt_Settings_Storage $settings_storage,
		Public_Cpt $cpt,
		Shortcode_Renderer $shortcode,
		Front_Assets $front_assets
	) {
		$this->settings_storage = $settings_storage;
		$this->cpt              = $cpt;
		$this->renderer         = new Cpt_Renderer( $shortcode, $front_assets );
	}

	/**
	 * Resolves the chosen id and dispatches to Cpt_Renderer::render()/render_preview()/
	 * get_empty_preview_placeholder() - the branching every CPT's *_Gutenberg_Block::render_block() shared verbatim.
	 *
	 * @param array<string,string> $attrs
	 */
	public function render_block( array $attrs, bool $is_admin_route ): string {
		$unique_id    = string( $attrs, 'id' );
		$cpt_settings = $this->settings_storage->get( $unique_id );

		if ( $cpt_settings->isLoaded() ) {
			return $is_admin_route ?
				$this->renderer->render_preview( $cpt_settings, $attrs ) :
				$this->renderer->render( $attrs );
		}

		return $is_admin_route ?
			$this->renderer->get_empty_preview_placeholder( $this->cpt->labels()->singular_name() ) :
			'';
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
					'slug'  => Cpt_Item_Picker::CATEGORY,
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
