<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide_Slider;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\CSS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide_Slider\Patterns\Div_Slider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide_Slider\Patterns\List_Slider;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\List_Element;

final class Splide_Slider_Library implements Frontend_Library, CSS_Library, JS_Library {
	public static function get_id(): string {
		return 'splide';
	}

	public static function get_label(): string {
		return __( 'Splide', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return 'https://splidejs.com/guides/options/';
	}

	public static function get_patterns(): array {
		return array(
			List_Element::class => array(
				List_Slider::class,
				Div_Slider::class,
			),
		);
	}

	public static function get_css_file_names(): array {
		return array(
			'splide',
		);
	}

	public static function get_css_patches( Asset_Resolver $resolver ): array {
		return array();
	}

	public static function get_js_files(): array {
		return array(
			'splide',
		);
	}
}
