<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Fields;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Field_Type;

defined( 'ABSPATH' ) || exit;

abstract class List_Field_Type extends Field_Type {
	const _TYPE_ = 'list';
}
