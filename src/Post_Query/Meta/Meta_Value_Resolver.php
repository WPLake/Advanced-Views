<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Meta;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;

class Meta_Value_Resolver {
	/**
	 * @return mixed
	 */
	public function resolve_meta_value( string $raw_value, Field_Meta $field_meta ) {
		return trim( $raw_value );
	}
}
