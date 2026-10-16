<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\File_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\JS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry\Patterns\Masonry_Pattern;
use Org\Wplake\Advanced_Views\Field_Value\Core\Media\Gallery_Value;

final class Avf_Masonry_Library implements Frontend_Library, JS_Library {
	public static function get_id(): string {
		return 'acf-views-masonry';
	}

	public static function get_label(): string {
		return __( 'Flat Masonry', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return 'https://gitlab.com/lightsource/masonry';
	}

	public static function get_patterns(): array {
		return array(
			Gallery_Value::class => array(
				Masonry_Pattern::class,
			),
		);
	}

	public static function get_js_files( File_Context $context ): array {
		return array(
			'acf-views-masonry',
		);
	}
}
