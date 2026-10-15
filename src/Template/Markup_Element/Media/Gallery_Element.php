<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Media;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\List_Element;

abstract class Gallery_Element extends List_Element {
	/**
	 * @return Image_Element[]
	 */
	abstract public function get_items(): array;
}
