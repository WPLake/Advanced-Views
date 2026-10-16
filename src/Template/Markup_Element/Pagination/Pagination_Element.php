<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Pagination;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\List_Element;

abstract class Pagination_Element extends List_Element {
	const TYPE         = 'type';
	const PAGES_AMOUNT = 'pages_amount';
	const CARD_ID      = 'card_id';

	/**
	 * Only the page numbers type has items, the other types return an empty array.
	 *
	 * @return Pagination_Page_Element[]
	 */
	abstract public function get_items(): array;

	abstract public function get_type(): string;

	abstract public function get_pages_amount(): string;

	abstract public function get_card_id(): string;

	public function get_properties(): array {
		$own_properties = array(
			self::TYPE         => fn() => $this->get_type(),
			self::PAGES_AMOUNT => fn() => $this->get_pages_amount(),
			self::CARD_ID      => fn() => $this->get_card_id(),
		);

		return array_merge( parent::get_properties(), $own_properties );
	}
}
