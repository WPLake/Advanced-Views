<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Media;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\Markup_Element;

final class Image_Element extends Markup_Element {
	const FULL_SIZE = 'full_size';
	const CAPTION   = 'caption';
	const WIDTH     = 'width';
	const HEIGHT    = 'height';

	public string $full_size = '';
	public string $caption   = '';
	public string $width     = '';
	public string $height    = '';
}
