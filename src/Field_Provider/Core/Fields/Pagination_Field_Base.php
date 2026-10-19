<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

defined( 'ABSPATH' ) || exit;

abstract class Pagination_Field_Base extends Markup_Field_Base {
	const TYPE         = 'type';
	const PAGES_AMOUNT = 'pages_amount';
	const CARD_ID      = 'card_id';
	const PAGE_NUMBER  = 'page_number';
}
