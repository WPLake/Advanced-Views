<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Field_Types;

defined( 'ABSPATH' ) || exit;

interface Image_Field_Type {
	const WIDTH     = 'width';
	const HEIGHT    = 'height';
	const CAPTION   = 'caption';
	const FULL_SIZE = 'full_size';
}
