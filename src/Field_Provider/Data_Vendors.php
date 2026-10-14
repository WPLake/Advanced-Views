<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Acf\Acf_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Woo\Fields\Woo_Fields;
use Org\Wplake\Advanced_Views\Field_Provider\Woo\Woo_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Wp\Wp_Data_Vendor;

class Data_Vendors extends Field_Provider_Cluster {
	protected function resolve_legacy_vendor_name( string $field_id ): string {
		if ( 0 !== strpos( $field_id, '_' ) ) {
			return Acf_Data_Vendor::NAME;
		}

		return 0 === strpos( $field_id, Woo_Fields::PREFIX ) ?
			Woo_Data_Vendor::NAME :
			Wp_Data_Vendor::NAME;
	}
}
