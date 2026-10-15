<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Macy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS_Library;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\Gallery_Element;

final class Macy_Library implements Frontend_Library, JS_Library {
	public static function get_id(): string {
		return 'macy';
	}

	public static function get_label(): string {
		return __( 'Macy', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return 'https://github.com/bigbite/macy.js';
	}

	public static function get_patterns(): array {
		return array(
			Gallery_Element::class => array(
				Macy_Pattern::class,
			),
		);
	}

	public static function get_js_file_names(): array {
		return array(
			'macy',
		);
	}

	public static function get_js_extension_urls(): array {
		return array();
	}
}
