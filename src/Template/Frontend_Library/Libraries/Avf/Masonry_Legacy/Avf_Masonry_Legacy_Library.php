<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry_Legacy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Instance_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\File_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Meta_JS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry_Legacy\Patterns\Masonry_Legacy_Pattern;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Gallery_Field;

final class Avf_Masonry_Legacy_Library implements Frontend_Library, Meta_JS_Library {
	public static function get_id(): string {
		return 'acf-views-masonry-legacy';
	}

	public static function get_label(): string {
		return __( 'Flat Masonry (legacy)', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return 'https://gitlab.com/lightsource/masonry';
	}

	public static function get_patterns(): array {
		return array(
			Gallery_Field::class => array(
				Masonry_Legacy_Pattern::class,
			),
		);
	}

	public static function get_js_files( File_Context $context ): array {
		return array(
			'acf-views-masonry-legacy',
		);
	}

	public static function get_js_instance_meta( Instance_Context $context ): array {
		return array(
			'selector'     => $context->get_item_selector( 'field' ),
			'rowMinHeight' => $context->get_masonry_row_min_height(),
			'gutter'       => $context->get_masonry_gutter(),
			'mobileGutter' => $context->get_masonry_mobile_gutter(),
			'bemName'      => $context->get_bem_name(),
		);
	}

	public static function get_js_meta( array $instances ): array {
		return array(
			'acfViewsMasonry' => array_values( $instances ),
		);
	}
}
