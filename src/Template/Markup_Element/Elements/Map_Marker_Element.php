<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Elements;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\Markup_Element;

final class Map_Marker_Element extends Markup_Element {
	const LAT   = 'lat';
	const LNG   = 'lng';
	const ICON  = 'icon';
	const TITLE = 'title';

	public string $lat   = '';
	public string $lng   = '';
	public string $icon  = '';
	public string $title = '';
}
