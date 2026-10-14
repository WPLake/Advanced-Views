<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Image_Element;

final class Inline_Gallery implements Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_outer_wrappers(): array {
		return array(
			'ul' => array(),
		);
	}

	public static function get_inner_wrappers(): array {
		return array(
			'li' => array(
				// fixme reuse.
				'#data-src'      => Image_Element::FULL_SIZE,
				'#data-sub-html' => Image_Element::CAPTION,
			),
		);
	}

	public static function get_js_settings(): array {
		return array(
			'showMaximizeIcon'  => true,
			'download'          => true,
			'enableDrag'        => false,
			'allowMediaOverlap' => false,
			'closable'          => false,
			'#plugins'          => array( 'window.lgThumbnail' ),
		);
	}

	public static function get_js_setup( JS_Variables $variables ): array {
		return array(
			'let container = document.createElement("div")',
			'container.classList.add("_lg-inline");',
			sprintf(
				'%s.parentElement.insertBefore(container, %1$s);',
				esc_html( $variables->field ),
			),
			sprintf(
				'new lightGallery(%s, %s).openGallery()',
				esc_html( $variables->field ),
				esc_html( $variables->settings )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'display'     => 'none',
			'._lg-inline' => array(
				'height' => '500px',
			),
		);
	}
}
