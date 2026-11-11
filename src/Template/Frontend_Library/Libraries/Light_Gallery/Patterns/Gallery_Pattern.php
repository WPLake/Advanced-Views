<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;

final class Gallery_Pattern implements Script_Pattern, Style_Pattern {
	public static function get_docs_url(): string {
		return 'https://www.lightgalleryjs.com/docs/settings/#lightgallery-core';
	}

	public static function get_js_options(): array {
		return array(
			'closeOnTap'        => true,
			'download'          => false,
			'allowMediaOverlap' => false,
			'enableDrag'        => false,
			'counter'           => true,
			'#plugins'          => '[window.lgThumbnail]',
		);
	}

	public static function get_js_setup( Script_Variables $variables ): array {
		return array(
			sprintf(
				'new lightGallery(%s, %s)',
				esc_html( $variables->outer_wrapper ),
				esc_html( $variables->options )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			'img:hover'  => array(
				'cursor' => 'zoom-in',
			),
		);
	}
}
