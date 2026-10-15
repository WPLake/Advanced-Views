<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Map;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\List_Element;

final class Map_Element extends List_Element {
	const ZOOM       = 'zoom';
	const CENTER_LAT = 'center_lat';
	const CENTER_LNG = 'center_lng';

	public string $zoom       = '';
	public string $center_lat = '';
	public string $center_lng = '';
}
