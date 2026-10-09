<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Acf\Acf_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Data_Vendors;
use Org\Wplake\Advanced_Views\Field_Provider\Meta_Box\Meta_Box_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Pods\Pods_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Woo\Woo_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Wp\Wp_Data_Vendor;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;

class Field_Provider_Factory extends Factory_Base {
	public function provider_cluster(): Field_Provider_Cluster {
		$cluster_class = $this->cluster_class();

		return new $cluster_class(
			$this->resolve( Logger::class ),
			$this->resolve( Instance_Container::class ),
			self::provider_classes()
		);
	}

	/**
	 * @return class-string<Field_Provider>[]
	 */
	public static function provider_classes(): array {
		return array(
			Wp_Data_Vendor::class,
			Woo_Data_Vendor::class,
			Acf_Data_Vendor::class,
			Meta_Box_Data_Vendor::class,
			Pods_Data_Vendor::class,
		);
	}

	/**
	 * @return class-string<Field_Provider_Cluster>
	 */
	protected function cluster_class(): string {
		return Data_Vendors::class;
	}

	public function integrations_loader(): Integrations_Loader {
		return new Integrations_Loader( $this->resolve( Field_Provider_Cluster::class ) );
	}
}
