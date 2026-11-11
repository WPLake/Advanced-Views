<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Concept\Libraries\Avf\Pagination\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Fields\Pagination_Field_Type;
use Org\Wplake\Advanced_Views\Template\Library_Concept\Core\Frontend_Pattern;
use Org\Wplake\Advanced_Views\Template\Library_Concept\Core\Patterns\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Page_Numbers_Pattern implements Frontend_Pattern, Template_Pattern {
	public static function get_id(): string {
		return 'page_numbers';
	}

	public static function get_label(): string {
		return __( 'Page numbers', 'acf-views' );
	}

	public static function get_outer_wrappers( Variable_Token $field ): array {
		$attributes = array(
			'class'                => 'avf-pagination',
			'data-pagination-type' => $field
				->clone()
				->add_item_path( Pagination_Field_Type::TYPE ),
			'data-pages-amount'    => $field
				->clone()
				->add_item_path( Pagination_Field_Type::PAGES_AMOUNT ),
			'data-card-id'         => $field
				->clone()
				->add_item_path( Pagination_Field_Type::CARD_ID ),
		);

		return array(
			tag( 'div' )
				->attributes( $attributes ),
		);
	}

	public static function get_inner_wrappers( Variable_Token $item ): array {
		return array();
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array(
			'class'            => 'avf-pagination__page',
			'data-page-number' => $item
				->clone()
				->add_item_path( Pagination_Field_Type::PAGE_NUMBER ),
		);
	}
}
