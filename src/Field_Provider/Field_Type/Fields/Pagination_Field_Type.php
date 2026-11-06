<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Field_Type;

defined( 'ABSPATH' ) || exit;

abstract class Pagination_Field_Type extends Field_Type {
	const _TYPE_ = 'pagination';

	const TYPE         = 'type';
	const PAGES_AMOUNT = 'pages_amount';
	const CARD_ID      = 'card_id';
	const PAGE_NUMBER  = 'page_number';
}
