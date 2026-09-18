<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Data_Vendors_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Acf_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Meta_Box_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods\Pods_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Woo_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Wp_Data_Vendor;

class Data_Vendors extends Data_Vendors_Base {
	/**
	 * @return Field_Provider[]
	 */
	protected function get_vendors(): array {
		return array(
			new Wp_Data_Vendor( $this->get_logger() ),
			new Woo_Data_Vendor( $this->get_logger() ),
			new Acf_Data_Vendor( $this->get_logger() ),
			new Meta_Box_Data_Vendor( $this->get_logger() ),
			new Pods_Data_Vendor( $this->get_logger() ),
		);
	}
}
