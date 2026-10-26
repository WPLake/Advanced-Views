<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Pagination\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Pagination_Field_Base;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Page_Numbers_Pattern implements Frontend_Pattern, Template_Pattern {
	public static function get_id(): string {
		return 'page_numbers';
	}

	public static function get_label(): string {
		return __( 'Page numbers', 'acf-views' );
	}

	public static function get_outer_wrappers(): array {
		return array(
			tag( 'div' )
				->attributes(
					array(
						'class'                 => 'avf-pagination',
						'#data-pagination-type' => Pagination_Field_Base::TYPE,
						'#data-pages-amount'    => Pagination_Field_Base::PAGES_AMOUNT,
						'#data-card-id'         => Pagination_Field_Base::CARD_ID,
					)
				),
		);
	}

	public static function get_inner_wrappers(): array {
		return array();
	}

	public static function get_item_attributes(): array {
		return array(
			'class'             => 'avf-pagination__page',
			'#data-page-number' => Pagination_Field_Base::PAGE_NUMBER,
		);
	}
}
