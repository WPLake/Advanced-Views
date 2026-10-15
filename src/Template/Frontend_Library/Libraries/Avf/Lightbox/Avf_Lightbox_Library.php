<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\CSS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Instance_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Meta_JS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Patterns\Gallery_Lightbox;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Patterns\Image_Lightbox;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\Gallery_Element;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\Image_Element;

final class Avf_Lightbox_Library implements Frontend_Library, CSS_Library, Meta_JS_Library {
	public static function get_id(): string {
		return 'acf-views-lightbox';
	}

	public static function get_label(): string {
		return __( 'Simple Lightbox', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return '';
	}

	public static function get_patterns(): array {
		return array(
			Image_Element::class   => array(
				Image_Lightbox::class,
			),
			Gallery_Element::class => array(
				Gallery_Lightbox::class,
			),
		);
	}

	public static function get_css_file_names(): array {
		return array(
			'acf-views-lightbox',
		);
	}

	public static function get_css_patches( Asset_Resolver $resolver ): array {
		return array();
	}

	public static function get_js_file_names(): array {
		return array(
			'acf-views-lightbox',
		);
	}

	public static function get_js_extension_urls(): array {
		return array();
	}

	public static function get_js_instance_meta( Instance_Context $context ): array {
		return array(
			'selector' => $context->get_item_selector( 'field' ),
		);
	}

	public static function get_js_meta( array $instances ): array {
		// selector as a key, to avoid duplicates (the same field can appear several times on a page).
		$unique_instances = array_column( $instances, null, 'selector' );
		$light_boxes      = array_values( $unique_instances );

		return array(
			'acfViewsLightBox' => $light_boxes,
		);
	}
}
