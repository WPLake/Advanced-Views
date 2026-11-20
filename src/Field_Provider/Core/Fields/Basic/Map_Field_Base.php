<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Basic;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;

defined( 'ABSPATH' ) || exit;

abstract class Map_Field_Base implements Markup_Field {
	use Markup_Field_Base;

	const LAT        = 'lat';
	const LNG        = 'lng';
	const ICON       = 'icon';
	const TITLE      = 'title';
	const ZOOM       = 'zoom';
	const CENTER_LAT = 'center_lat';
	const CENTER_LNG = 'center_lng';
}
