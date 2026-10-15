<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\JS\JS_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;

final class Inline_Gallery implements Frontend_Pattern, Template_Pattern, JS_Pattern, CSS_Pattern {
	public static function get_id(): string {
		return 'inline_gallery';
	}

	public static function get_label(): string {
		return __( 'Inline Gallery', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			'ul' => array(),
		);
	}

	public static function get_inner_wrappers(): array {
		return array(
			'li' => Lightbox_Single::DATA_PROPERTIES,
		);
	}

	public static function get_element_attributes(): array {
		return array();
	}

	public static function get_js_options(): array {
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
			'container.classList.add("_lg-inline")',
			'',
			sprintf(
				'%s = {...%1$s, container: container}',
				esc_html( $variables->options ),
			),
			sprintf(
				'%s.parentElement.insertBefore(container, %1$s)',
				esc_html( $variables->outer_wrapper ),
			),
			'',
			sprintf(
				'new lightGallery(%s, %s).openGallery()',
				esc_html( $variables->outer_wrapper ),
				esc_html( $variables->options )
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
