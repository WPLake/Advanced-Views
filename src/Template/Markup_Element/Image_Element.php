<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element;

defined( 'ABSPATH' ) || exit;

final class Image_Element {
	const FULL_SIZE = 'full_size';
	const CAPTION   = 'caption';

	public string $full_size = '';
	public string $caption   = '';
}
