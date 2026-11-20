<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Basic;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;

defined( 'ABSPATH' ) || exit;

abstract class Pagination_Field_Base implements Markup_Field {
	use Markup_Field_Base;

	const TYPE         = 'type';
	const PAGES_AMOUNT = 'pages_amount';
	const CARD_ID      = 'card_id';
	const PAGE_NUMBER  = 'page_number';
}
