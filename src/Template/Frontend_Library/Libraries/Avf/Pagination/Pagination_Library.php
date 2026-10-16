<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Pagination;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\JS\File_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Instance_Context;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Types\Meta_JS\Meta_JS_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Pagination\Patterns\Page_Numbers_Pattern;
use Org\Wplake\Advanced_Views\Field_Value\Core\Pagination\Pagination_Value;

class Pagination_Library implements Frontend_Library, Meta_JS_Library {
	public static function get_id(): string {
		return 'acf-views-pagination';
	}

	public static function get_label(): string {
		return __( 'Pagination', 'acf-views' );
	}

	public static function get_docs_url(): string {
		return '';
	}

	public static function get_patterns(): array {
		return array(
			Pagination_Value::class => static::get_pagination_patterns(),
		);
	}

	public static function get_js_files( File_Context $context ): array {
		return array(
			'acf-views-pagination',
		);
	}

	public static function get_js_instance_meta( Instance_Context $context ): array {
		return array(
			'selector'     => $context->get_component_selector(),
			'elementClass' => $context->get_bem_name(),
			'ajaxData'     => array(
				'url'  => $context->get_load_more_ajax_url(),
				'name' => $context->get_load_more_ajax_action(),
			),
		);
	}

	public static function get_js_meta( array $instances ): array {
		return array(
			'acfViewsPagination' => array(
				'instances' => array_values( $instances ),
			),
		);
	}

	/**
	 * @return class-string<Frontend_Pattern>[]
	 */
	protected static function get_pagination_patterns(): array {
		return array(
			Page_Numbers_Pattern::class,
		);
	}
}
