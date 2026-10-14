<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Elements;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\Markup_Element_Base;

final class Image_Element extends Markup_Element_Base {
	const KIND      = 'image';
	const FULL_SIZE = 'full_size';
	const CAPTION   = 'caption';

	public string $full_size = '';
	public string $caption   = '';
}
