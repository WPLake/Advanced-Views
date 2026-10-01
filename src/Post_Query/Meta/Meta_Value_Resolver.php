<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Meta;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Meta_Field_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;

class Meta_Value_Resolver {
	public function is_supported_field( Meta_Field_Settings $field, Field_Meta $field_meta ): bool {
		// query only existing and non-virtual, e.g. Woo_Fields::FIELD_FEATURED is a taxonomy.
		return $field_meta->is_field_exist() &&
						strlen( $field_meta->get_name() ) > 0 &&
			$this->is_supported_value( $field );
	}

	/**
	 * @return mixed
	 */
	public function resolve_meta_value( string $raw_value, Field_Meta $field_meta ) {
		return trim( $raw_value );
	}

	protected function is_supported_value( Meta_Field_Settings $field ): bool {
		// in Lite only Literal values are supported.
		return ! $field->is_dynamic_value();
	}
}
