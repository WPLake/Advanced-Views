<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Maps;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\File_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Instance_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Meta_JS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Maps\Patterns\Map_Pattern;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Elements\Map_Element;

final class Avf_Maps_Library implements Frontend_Library, Meta_JS_Library {
	public static function get_id(): string {
		return 'acf-views-maps';
	}

	public static function get_label(): string {
		return __( 'Google Maps', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return 'https://developers.google.com/maps/documentation/javascript';
	}

	public static function get_patterns(): array {
		return array(
			Map_Element::class => array(
				Map_Pattern::class,
			),
		);
	}

	public static function get_js_files( File_Context $context ): array {
		$api_key = $context->get_google_map_api_key();

		// the callback is defined in the acf-views-maps file, so the order matters.
		$google_url = sprintf(
			'https://maps.googleapis.com/maps/api/js?key=%s&callback=acfViewsGoogleMaps',
			rawurlencode( $api_key )
		);

		return array(
			'acf-views-maps',
			$google_url,
		);
	}

	public static function get_js_instance_meta( Instance_Context $context ): array {
		return array(
			'selector' => $context->get_item_selector( 'map' ),
		);
	}

	public static function get_js_meta( array $instances ): array {
		$selectors = array_column( $instances, 'selector' );
		$selectors = array_unique( $selectors );

		return array(
			'acfViewsMaps' => array_values( $selectors ),
		);
	}
}
