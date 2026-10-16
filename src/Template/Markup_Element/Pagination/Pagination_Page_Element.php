<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Pagination;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\Markup_Element;

abstract class Pagination_Page_Element implements Markup_Element {
	const PAGE_NUMBER = 'page_number';

	abstract public function get_page_number(): string;

	public function get_properties(): array {
		return array(
			self::PAGE_NUMBER => fn() => $this->get_page_number(),
		);
	}
}
