<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Value\Core\Media;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Value\Core\List_Value;

abstract class Gallery_Value extends List_Value {
	/**
	 * @return Image_Value[]
	 */
	abstract public function get_items(): array;
}
