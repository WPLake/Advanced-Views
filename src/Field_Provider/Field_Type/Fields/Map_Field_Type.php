<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Field_Type;

defined( 'ABSPATH' ) || exit;

abstract class Map_Field_Type extends Field_Type {
	const _TYPE_ = 'map';

	const LAT        = 'lat';
	const LNG        = 'lng';
	const ICON       = 'icon';
	const TITLE      = 'title';
	const ZOOM       = 'zoom';
	const CENTER_LAT = 'center_lat';
	const CENTER_LNG = 'center_lng';
}
