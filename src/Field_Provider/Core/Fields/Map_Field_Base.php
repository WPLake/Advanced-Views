<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Fields\Map_Field_Type;

defined( 'ABSPATH' ) || exit;

abstract class Map_Field_Base extends Map_Field_Type implements Markup_Field {
	use Markup_Field_Base;
}
