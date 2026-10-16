<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Pagination\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Pagination\Pagination_Element;
use Org\Wplake\Advanced_Views\Template\Markup_Element\Pagination\Pagination_Page_Element;

final class Page_Numbers_Pattern implements Frontend_Pattern, Template_Pattern {
	public static function get_id(): string {
		return 'page_numbers';
	}

	public static function get_label(): string {
		return __( 'Page numbers', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			array(
				'tag'   => 'div',
				'attrs' => array(
					'class'                 => 'avf-pagination',
					'#data-pagination-type' => Pagination_Element::TYPE,
					'#data-pages-amount'    => Pagination_Element::PAGES_AMOUNT,
					'#data-card-id'         => Pagination_Element::CARD_ID,
				),
			),
		);
	}

	public static function get_inner_wrappers(): array {
		return array();
	}

	public static function get_item_attributes(): array {
		return array(
			'class'             => 'avf-pagination__page',
			'#data-page-number' => Pagination_Page_Element::PAGE_NUMBER,
		);
	}
}
